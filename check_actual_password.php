<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;
use Illuminate\Support\Facades\Crypt;

echo "=== ROUTER PASSWORD ANALYSIS ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Router IP: {$router->ip_address}\n";
echo "Username: {$router->username}\n";
echo "Encrypted password: {$router->password}\n";
echo "Encrypted password length: " . strlen($router->password) . "\n\n";

try {
    $decryptedPassword = Crypt::decryptString($router->password);
    echo "Decrypted password: '{$decryptedPassword}'\n";
    echo "Decrypted password length: " . strlen($decryptedPassword) . "\n";
    echo "Password bytes: ";
    for ($i = 0; $i < strlen($decryptedPassword); $i++) {
        echo ord($decryptedPassword[$i]) . " ";
    }
    echo "\n";
    echo "Password hex: " . bin2hex($decryptedPassword) . "\n";
    echo "Password is empty: " . (empty($decryptedPassword) ? 'YES' : 'NO') . "\n";
    echo "Password equals empty string: " . ($decryptedPassword === '' ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "Failed to decrypt: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING WITH ACTUAL PASSWORD ===\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

try {
    $actualPassword = Crypt::decryptString($router->password);
    echo "Testing connection with actual password: '{$actualPassword}'\n";
    
    $result = $mikrotikService->connect(
        $router->ip_address, 
        $router->username, 
        $actualPassword, 
        $router->port
    );
    
    echo "Connection result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== LOG ANALYSIS ===\n";

$logFile = storage_path('logs/laravel.log');
$logContent = file_get_contents($logFile);
$lines = explode("\n", $logContent);

// Cari log terakhir tentang Test Connection
$lastLogs = array_slice($lines, -50);
foreach ($lastLogs as $line) {
    if (strpos($line, 'Router test connection') !== false) {
        echo "Recent log: $line\n";
    }
}

echo "\nAnalysis complete.\n";
