<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Customer;
use App\Models\Payment;

echo "=== TESTING PAYMENT SYSTEM ===\n\n";

// Get first customer
$customer = Customer::with('package')->first();
if (!$customer) {
    echo "❌ No customer found. Please create a customer first.\n";
    exit;
}

echo "Customer found: {$customer->name}\n";
echo "Package: {$customer->package->name}\n";
echo "Price: Rp " . number_format($customer->package->price, 0, ',', '.') . "\n";
echo "Installation Date: {$customer->installation_date}\n\n";

// Create initial payment
echo "Creating initial payment...\n";
try {
    $payment = Payment::create([
        'customer_id' => $customer->id,
        'amount' => $customer->package->price,
        'billing_date' => $customer->installation_date,
        'due_date' => $customer->installation_date, // Pra-bayar: bayar saat pasang
        'status' => 'pending',
        'notes' => 'Pembayaran pertama saat pemasangan (manual test)',
        'created_by' => $customer->created_by
    ]);
    
    echo "✅ Payment created successfully!\n";
    echo "Invoice Number: {$payment->invoice_number}\n";
    echo "Amount: {$payment->getFormattedAmount()}\n";
    echo "Due Date: {$payment->due_date->format('d/m/Y')}\n";
    echo "Status: {$payment->getStatusLabel()}\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating payment: " . $e->getMessage() . "\n\n";
}

// Check if overdue
echo "=== CHECKING OVERDUE STATUS ===\n";
$payments = Payment::where('customer_id', $customer->id)->get();
foreach ($payments as $payment) {
    echo "Payment {$payment->invoice_number}:\n";
    echo "- Due Date: {$payment->due_date->format('d/m/Y')}\n";
    echo "- Status: {$payment->getStatusLabel()}\n";
    echo "- Is Overdue: " . ($payment->isOverdue() ? 'YES' : 'NO') . "\n";
    if ($payment->isOverdue()) {
        echo "- Days Overdue: {$payment->getDaysOverdue()}\n";
    }
    echo "\n";
}

echo "=== PAYMENT STATISTICS ===\n";
echo "Total Pending: " . Payment::where('status', 'pending')->count() . "\n";
echo "Total Overdue: " . Payment::where('status', 'pending')->where('due_date', '<', now()->toDateString())->count() . "\n";
echo "Total Paid: " . Payment::where('status', 'paid')->count() . "\n";
echo "Total Outstanding Amount: Rp " . number_format(Payment::where('status', 'pending')->sum('amount'), 0, ',', '.') . "\n";

echo "\n✅ Payment system test completed!\n";
