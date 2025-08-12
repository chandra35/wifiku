<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Payment;

echo "=== DEBUG PAYMENT BUTTON ===\n\n";

// Check if payments exist
$payments = Payment::with('customer')->where('status', 'pending')->get();

echo "Found " . $payments->count() . " pending payments:\n\n";

foreach ($payments as $payment) {
    echo "Payment ID: {$payment->id}\n";
    echo "Invoice: {$payment->invoice_number}\n";
    echo "Customer: {$payment->customer->name}\n";
    echo "Status: {$payment->status}\n";
    echo "URL for mark as paid: /payments/{$payment->id}/mark-as-paid\n";
    echo "---\n";
}

// Test route exists
echo "\n=== CHECKING ROUTES ===\n";
$routes = \Route::getRoutes();
$paymentRoutes = [];

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'payments')) {
        $paymentRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName()
        ];
    }
}

foreach ($paymentRoutes as $route) {
    echo "{$route['method']} {$route['uri']} -> {$route['name']}\n";
}

echo "\n✅ Debug completed!\n";
