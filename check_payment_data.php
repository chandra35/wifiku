<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Payment;

echo "=== CHECKING PAYMENT DATA ===\n\n";

$allPayments = Payment::with('customer')->get();
echo "Total payments in database: " . $allPayments->count() . "\n\n";

foreach ($allPayments as $payment) {
    echo "ID: {$payment->id}\n";
    echo "Invoice: {$payment->invoice_number}\n";
    echo "Customer: {$payment->customer->name}\n";
    echo "Status: {$payment->status}\n";
    echo "Due Date: {$payment->due_date}\n";
    echo "Amount: Rp " . number_format($payment->amount, 0, ',', '.') . "\n";
    echo "Is Overdue: " . ($payment->isOverdue() ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

// Test payment status filtering
echo "\n=== STATUS BREAKDOWN ===\n";
echo "Pending: " . Payment::where('status', 'pending')->count() . "\n";
echo "Paid: " . Payment::where('status', 'paid')->count() . "\n";
echo "Overdue: " . Payment::where('status', 'overdue')->count() . "\n";

// Check if we can access payment controller route
echo "\n=== TESTING PAYMENT ACCESS ===\n";
$pendingPayment = Payment::where('status', 'pending')->first();
if ($pendingPayment) {
    echo "Found pending payment: {$pendingPayment->invoice_number}\n";
    echo "Payment URL: /payments/{$pendingPayment->id}\n";
    echo "Mark as paid URL: /payments/{$pendingPayment->id}/mark-as-paid\n";
} else {
    echo "No pending payments found\n";
}

echo "\n✅ Payment data check completed!\n";
