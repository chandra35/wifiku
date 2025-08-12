<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Bulk pay selected payments
     */
    public function bulkPay(Request $request)
    {
        $user = auth()->user();
        $paymentIds = $request->input('payment_ids', []);
        if (empty($paymentIds)) {
            return redirect()->back()->with('error', 'Tidak ada tagihan yang dipilih.');
        }

        $payments = Payment::whereIn('id', $paymentIds)->get();
        $paidCount = 0;
        $customer = null;
        DB::beginTransaction();
        try {
            foreach ($payments as $payment) {
                // Only allow if user can update
                if ($user->role->name === 'super_admin' || $payment->customer->created_by === $user->id) {
                    if ($payment->status !== 'paid') {
                        $payment->markAsPaid($user->id);
                        $paidCount++;
                        $customer = $payment->customer; // simpan customer terakhir
                        if ($payment->customer->status === 'suspended') {
                            $payment->customer->update(['status' => 'active']);
                        }
                    }
                }
            }
            // Tidak perlu generateNextPayment, karena generateMissingPayments sudah menjamin tagihan selalu ada
            DB::commit();
            return redirect()->route('payments.index')->with('success', $paidCount.' tagihan berhasil dibayar.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membayar tagihan: '.$e->getMessage());
        }
    }

    /**
     * Generate invoice for selected payments
     */
    public function invoice(Request $request)
    {
        $ids = $request->get('ids');
        if (!$ids) {
            abort(404);
        }
        $idArr = explode(',', $ids);
        $payments = Payment::with(['customer.package'])
            ->whereIn('id', $idArr)
            ->orderBy('billing_date', 'asc')
            ->get();
        if ($payments->isEmpty()) {
            abort(404);
        }
        $customer = $payments->first()->customer;
        return view('payments.invoice', compact('payments', 'customer'));
    }
    /**
     * Display a listing of pending payments (Belum Bayar)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', 'pending');
        $customers = Customer::orderBy('name');
        if ($user->role->name !== 'super_admin') {
            $customers = $customers->where('created_by', $user->id);
        }
        $customers = $customers->get();
        // Generate missing payments untuk semua customer (agar tagihan dari instalasi sampai bulan berjalan selalu ada)
        foreach ($customers as $cust) {
            $this->generateMissingPayments($cust);
        }
        if ($status === 'overdue') {
            // Ambil pelanggan yang punya tagihan overdue
            $overdueCustomers = $customers->filter(function($cust) {
                return $cust->getOverduePayments()->count() > 0;
            });
            // Pagination manual (karena collection)
            $page = $request->get('page', 1);
            $perPage = 20;
            $paged = $overdueCustomers->forPage($page, $perPage);
            $payments = new \Illuminate\Pagination\LengthAwarePaginator($paged, $overdueCustomers->count(), $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            return view('payments.index', [
                'payments' => $payments,
                'status' => $status,
                'customers' => $customers
            ]);
        } else {
            // Base query for payments
            $query = Payment::with(['customer.package'])
                        ->join('customers', 'payments.customer_id', '=', 'customers.id');
            if ($user->role->name !== 'super_admin') {
                $query->where('customers.created_by', $user->id);
            }
            if ($status === 'pending') {
                $now = now();
                $query->where('payments.status', 'pending')
                    ->whereMonth('payments.billing_date', $now->month)
                    ->whereYear('payments.billing_date', $now->year);
            } else {
                $query->where('payments.status', $status);
            }
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->where('customers.id', $request->customer_id);
            }
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('customers.name', 'like', "%{$search}%")
                    ->orWhere('customers.customer_id', 'like', "%{$search}%")
                    ->orWhere('payments.invoice_number', 'like', "%{$search}%");
                });
            }
            $payments = $query->select('payments.*')
                            ->orderBy('payments.due_date', 'asc')
                            ->orderBy('payments.created_at', 'desc')
                            ->paginate(20);
            $this->updateOverduePayments();
            return view('payments.index', compact('payments', 'status', 'customers'));
        }
    }

    /**
     * Generate missing payments for a customer from installation to current month
     */
    private function generateMissingPayments(Customer $customer)
    {
        $start = Carbon::parse($customer->installation_date)->startOfMonth();
        $now = now()->startOfMonth();
        $months = [];
        while ($start <= $now) {
            $months[] = $start->copy();
            $start->addMonth();
        }
        foreach ($months as $month) {
            $exists = Payment::where('customer_id', $customer->id)
                ->whereYear('billing_date', $month->year)
                ->whereMonth('billing_date', $month->month)
                ->exists();
            if (!$exists) {
                Payment::create([
                    'customer_id' => $customer->id,
                    'amount' => $customer->package->price,
                    'billing_date' => $month->toDateString(),
                    'due_date' => $month->toDateString(),
                    'status' => 'pending',
                    'notes' => 'Tagihan otomatis',
                    'created_by' => $customer->created_by
                ]);
            }
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $user = auth()->user();
        
        // Check if user can view this payment
        if ($user->role->name !== 'super_admin' && $payment->customer->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $payment->load(['customer.package', 'createdBy', 'confirmedBy']);
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Payment $payment)
    {
        $user = auth()->user();
        
        // Check if user can update this payment
        if ($user->role->name !== 'super_admin' && $payment->customer->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            // Mark payment as paid
            $payment->markAsPaid($user->id);
            // Update customer status to active if was suspended
            if ($payment->customer->status === 'suspended') {
                $payment->customer->update(['status' => 'active']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi. Pelanggan sudah aktif kembali.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal konfirmasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    * (Deprecated) Generate next payment for customer. Tidak dipakai lagi, digantikan oleh generateMissingPayments.
    */
    // public function generateNextPayment(Customer $customer)
    // {
    //     // Deprecated: handled by generateMissingPayments
    // }

    /**
     * Calculate next billing date based on billing cycle
     */
    private function calculateNextBillingDate(Customer $customer): string
    {
        $lastPayment = $customer->payments()
                              ->where('status', 'paid')
                              ->orderBy('billing_date', 'desc')
                              ->first();
        
        $baseDate = $lastPayment 
                   ? Carbon::parse($lastPayment->billing_date)
                   : Carbon::parse($customer->installation_date);
        
        return match($customer->billing_cycle) {
            'monthly' => $baseDate->addMonth()->toDateString(),
            'quarterly' => $baseDate->addMonths(3)->toDateString(),
            'semi-annual' => $baseDate->addMonths(6)->toDateString(),
            'annual' => $baseDate->addYear()->toDateString(),
            default => $baseDate->addMonth()->toDateString()
        };
    }

    /**
     * Auto-suspend customers with overdue payments
     */
    public function autoSuspendOverdueCustomers()
    {
        $overduePayments = Payment::with('customer')
                                 ->where('status', 'pending')
                                 ->where('due_date', '<', now()->subDays(3)->toDateString()) // Grace period 3 hari
                                 ->get();
        
        $suspendedCount = 0;
        
        foreach ($overduePayments as $payment) {
            if ($payment->customer->status === 'active') {
                $payment->customer->update(['status' => 'suspended']);
                $payment->update(['status' => 'overdue']);
                $suspendedCount++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$suspendedCount} pelanggan berhasil di-suspend karena terlambat bayar."
        ]);
    }

    /**
     * Update overdue payments status
     */
    private function updateOverduePayments()
    {
        Payment::where('status', 'pending')
               ->where('due_date', '<', now()->toDateString())
               ->update(['status' => 'overdue']);
    }

    /**
     * Create initial payment for new customer
     */
    public static function createInitialPayment(Customer $customer)
    {
        // Create payment for installation month (pra-bayar)
        Payment::create([
            'customer_id' => $customer->id,
            'amount' => $customer->package->price,
            'billing_date' => $customer->installation_date,
            'due_date' => $customer->installation_date, // Pra-bayar: bayar saat pasang
            'status' => 'pending',
            'notes' => 'Pembayaran pertama saat pemasangan',
            'created_by' => $customer->created_by
        ]);

        // Jika next_billing_date berbeda dengan installation_date, buat payment untuk bulan berikutnya
        if ($customer->next_billing_date && $customer->next_billing_date != $customer->installation_date) {
            $exists = Payment::where('customer_id', $customer->id)
                ->where('billing_date', $customer->next_billing_date)
                ->exists();
            if (!$exists) {
                Payment::create([
                    'customer_id' => $customer->id,
                    'amount' => $customer->package->price,
                    'billing_date' => $customer->next_billing_date,
                    'due_date' => $customer->next_billing_date,
                    'status' => 'pending',
                    'notes' => 'Tagihan bulan berikutnya',
                    'created_by' => $customer->created_by
                ]);
            }
        }
    }
}
