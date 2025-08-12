<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Customer;
use App\Models\Payment;

echo "=== CREATING MORE TEST PAYMENTS ===\n\n";

$customers = Customer::with('package')->take(3)->get();

foreach ($customers as $customer) {
    echo "Creating payments for: {$customer->name}\n";
    
    // Create overdue payment (1 month ago)
    $overduePayment = Payment::create([
        'customer_id' => $customer->id,
        'amount' => $customer->package->price,
        'billing_date' => now()->subMonth()->toDateString(),
        'due_date' => now()->subMonth()->toDateString(),
        'status' => 'pending',
        'notes' => 'Pembayaran bulan lalu (overdue)',
        'created_by' => $customer->created_by
    ]);
    echo "  ✅ Overdue payment: {$overduePayment->invoice_number}\n";
    
    // Create current payment (this month)
    $currentPayment = Payment::create([
        'customer_id' => $customer->id,
        'amount' => $customer->package->price,
        'billing_date' => now()->toDateString(),
        'due_date' => now()->toDateString(),
        'status' => 'pending',
        'notes' => 'Pembayaran bulan ini',
        'created_by' => $customer->created_by
    ]);
    echo "  ✅ Current payment: {$currentPayment->invoice_number}\n";
    
    // Mark one payment as paid for variation
    if ($customer->name === 'Hisyam') {
        $currentPayment->markAsPaid($customer->created_by);
        echo "  💰 Marked as paid: {$currentPayment->invoice_number}\n";
    }
    
    echo "\n";
}

echo "=== FINAL STATISTICS ===\n";
echo "Total Pending: " . Payment::where('status', 'pending')->count() . "\n";
echo "Total Overdue: " . Payment::where('status', 'pending')->where('due_date', '<', now()->toDateString())->count() . "\n";
echo "Total Paid: " . Payment::where('status', 'paid')->count() . "\n";
echo "Total Outstanding: Rp " . number_format(Payment::where('status', 'pending')->sum('amount'), 0, ',', '.') . "\n";

echo "\n✅ Test data created! Now you can visit /payments to see the 'Belum Bayar' page.\n";
