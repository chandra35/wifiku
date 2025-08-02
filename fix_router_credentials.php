<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;
use Illuminate\Support\Facades\Crypt;

echo "=== FIXING ROUTER CREDENTIALS ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Current router settings:\n";
echo "IP: {$router->ip_address}\n";
echo "Username: {$router->username}\n";
echo "Port: {$router->port}\n";

try {
    $currentPassword = Crypt::decryptString($router->password);
    echo "Current password: '$currentPassword'\n";
} catch (Exception $e) {
    echo "Failed to decrypt current password: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING DARI WEB BERHASIL ===\n";
echo "Berdasarkan log, Test Connection berhasil dengan:\n";
echo "- Username: web\n";
echo "- Password: (kosong dari form input)\n\n";

echo "Tapi yang disimpan di database:\n";
echo "- Username: web ✓ (sudah benar)\n";
echo "- Password: 'admin' ✗ (salah)\n\n";

echo "Mari kita perbaiki password di database...\n";

// Encrypt password kosong
$emptyPassword = Crypt::encryptString('');
echo "Empty password encrypted: $emptyPassword\n";

// Update router dengan password kosong
echo "\nUpdating router password to empty string...\n";
$router->password = $emptyPassword;
$router->save();

echo "Router updated!\n";

echo "\n=== VERIFICATION ===\n";
$updatedRouter = Router::first();
$newPassword = Crypt::decryptString($updatedRouter->password);
echo "New password: '$newPassword' (length: " . strlen($newPassword) . ")\n";
echo "Is empty: " . (empty($newPassword) ? 'YES' : 'NO') . "\n";

echo "\n=== TESTING MIKROTIK SERVICE ===\n";
$mikrotikService = app(\App\Services\MikrotikService::class);

echo "Testing updated credentials...\n";
try {
    $result = $mikrotikService->testConnection(
        $updatedRouter->ip_address,
        $updatedRouter->username,
        $newPassword,
        $updatedRouter->port
    );
    
    echo "Test result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Router credentials updated to match successful Test Connection:\n";
echo "- Username: {$updatedRouter->username}\n";
echo "- Password: (empty string)\n";
echo "- IP: {$updatedRouter->ip_address}\n";
echo "- Port: {$updatedRouter->port}\n";

echo "\nSekarang coba refresh halaman Router Management untuk melihat status.\n";
