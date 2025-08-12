<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Payment;
use App\Models\Customer;

echo "=== CLEANING OLD PAYMENTS AND CREATING NEW TEST DATA ===\n\n";

// Clear old payments
Payment::truncate();
echo "✅ Cleared old payments\n";

$customers = Customer::with('package')->take(4)->get();

foreach ($customers as $index => $customer) {
    echo "Creating payments for: {$customer->name}\n";
    
    // Create different scenarios for each customer
    switch ($index) {
        case 0: // Customer 1: Has overdue payment
            $payment = Payment::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'billing_date' => now()->subMonth()->toDateString(),
                'due_date' => now()->subMonth()->toDateString(),
                'status' => 'pending', // Will be overdue
                'notes' => 'Pembayaran terlambat',
                'created_by' => $customer->created_by
            ]);
            echo "  📅 Overdue payment: {$payment->invoice_number}\n";
            break;
            
        case 1: // Customer 2: Has current pending payment
            $payment = Payment::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'billing_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(), // Due in 5 days
                'status' => 'pending',
                'notes' => 'Pembayaran bulan ini',
                'created_by' => $customer->created_by
            ]);
            echo "  ⏰ Pending payment: {$payment->invoice_number}\n";
            break;
            
        case 2: // Customer 3: Has paid payment
            $payment = Payment::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'billing_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'status' => 'paid',
                'paid_date' => now()->toDateString(),
                'notes' => 'Sudah dibayar',
                'created_by' => $customer->created_by,
                'confirmed_by' => $customer->created_by
            ]);
            echo "  💰 Paid payment: {$payment->invoice_number}\n";
            break;
            
        case 3: // Customer 4: Multiple payments
            // Overdue payment
            $overdue = Payment::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'billing_date' => now()->subMonths(2)->toDateString(),
                'due_date' => now()->subMonths(2)->toDateString(),
                'status' => 'overdue',
                'notes' => 'Pembayaran 2 bulan lalu',
                'created_by' => $customer->created_by
            ]);
            echo "  🔴 Old overdue: {$overdue->invoice_number}\n";
            
            // Current pending
            $pending = Payment::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'billing_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'status' => 'pending',
                'notes' => 'Pembayaran bulan ini',
                'created_by' => $customer->created_by
            ]);
            echo "  🟡 Current pending: {$pending->invoice_number}\n";
            break;
    }
    echo "\n";
}

echo "=== FINAL STATISTICS ===\n";
echo "Total Payments: " . Payment::count() . "\n";
echo "Pending: " . Payment::where('status', 'pending')->count() . "\n";
echo "Overdue: " . Payment::where('status', 'overdue')->count() . "\n";
echo "Paid: " . Payment::where('status', 'paid')->count() . "\n";
echo "Total Outstanding: Rp " . number_format(Payment::whereIn('status', ['pending', 'overdue'])->sum('amount'), 0, ',', '.') . "\n";

echo "\n=== TESTING PAYMENT METHODS ===\n";
$testPayment = Payment::where('status', 'pending')->first();
if ($testPayment) {
    echo "Test Payment: {$testPayment->invoice_number}\n";
    echo "Status: {$testPayment->status}\n";
    echo "Due Date: {$testPayment->due_date}\n";
    echo "Is Overdue: " . ($testPayment->isOverdue() ? 'YES' : 'NO') . "\n";
    echo "Days Overdue: {$testPayment->getDaysOverdue()}\n";
    echo "Status Label: {$testPayment->getStatusLabel()}\n";
    echo "Badge Class: {$testPayment->getStatusBadgeClass()}\n";
}

echo "\n✅ Test data created! Now visit /payments to test the payment buttons.\n";
