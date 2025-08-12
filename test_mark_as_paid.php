<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate POST request to mark as paid
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Controllers\PaymentController;

echo "=== TESTING MARK AS PAID FUNCTIONALITY ===\n\n";

// Get a pending payment
$payment = Payment::where('status', 'pending')->first();
if (!$payment) {
    echo "❌ No pending payment found to test\n";
    exit;
}

echo "Testing payment: {$payment->invoice_number}\n";
echo "Customer: {$payment->customer->name}\n";
echo "Current status: {$payment->status}\n";
echo "Amount: {$payment->getFormattedAmount()}\n\n";

// Test manual mark as paid
try {
    echo "Attempting to mark as paid...\n";
    
    // Simulate logged in user
    $user = \App\Models\User::first();
    if (!$user) {
        echo "❌ No user found for testing\n";
        exit;
    }
    
    auth()->login($user);
    echo "✅ Logged in as: {$user->name}\n";
    
    // Test the mark as paid functionality
    $result = $payment->markAsPaid($user->id);
    
    if ($result) {
        // Refresh payment from database
        $payment->refresh();
        echo "✅ Payment marked as paid successfully!\n";
        echo "New status: {$payment->status}\n";
        echo "Paid date: {$payment->paid_date}\n";
        echo "Confirmed by: {$payment->confirmedBy->name}\n";
    } else {
        echo "❌ Failed to mark payment as paid\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTING CONTROLLER METHOD ===\n";

// Test the controller method directly
try {
    $controller = new PaymentController();
    
    // Create mock request
    $request = Request::create(
        '/payments/' . $payment->id . '/mark-as-paid',
        'POST',
        ['_token' => csrf_token()]
    );
    
    // Set the authenticated user
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Call the method
    $response = $controller->markAsPaid($payment);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Controller response:\n";
        echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        echo "Message: " . ($data['message'] ?? 'No message') . "\n";
    } else {
        echo "Unexpected response type: " . get_class($response) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Controller error: " . $e->getMessage() . "\n";
}

echo "\n✅ Testing completed!\n";
