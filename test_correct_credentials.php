<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;

echo "=== TESTING CORRECT MIKROTIK CREDENTIALS ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Current router in database:\n";
echo "Username: {$router->username}\n";
echo "IP: {$router->ip_address}\n";
echo "Port: {$router->port}\n\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

echo "Testing different username/password combinations:\n\n";

// Test 1: web dengan password kosong (sesuai log yang berhasil)
echo "1. Testing username 'web' with empty password:\n";
try {
    $result = $mikrotikService->connect($router->ip_address, 'web', '', $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

// Test 2: admin dengan password kosong
echo "\n2. Testing username 'admin' with empty password:\n";
try {
    $result = $mikrotikService->connect($router->ip_address, 'admin', '', $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

// Test 3: web dengan password admin
echo "\n3. Testing username 'web' with password 'admin':\n";
try {
    $result = $mikrotikService->connect($router->ip_address, 'web', 'admin', $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

// Test 4: admin dengan password admin
echo "\n4. Testing username 'admin' with password 'admin':\n";
try {
    $result = $mikrotikService->connect($router->ip_address, 'admin', 'admin', $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUSION ===\n";
echo "Jika test 1 berhasil, berarti password yang benar adalah:\n";
echo "Username: web\n";
echo "Password: (kosong)\n\n";

echo "Data yang perlu diperbaiki di database:\n";
echo "Username harus diubah dari '{$router->username}' ke 'web'\n";
echo "Password harus diubah menjadi kosong (encrypt empty string)\n";

echo "\nAnalysis complete.\n";
