<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;
use App\Models\Router;

echo "=== ANALYZING CONNECTION LOGS ===\n\n";

// Baca log file
$logFile = storage_path('logs/laravel.log');
if (!file_exists($logFile)) {
    echo "Log file not found: $logFile\n";
    exit;
}

// Cari 10 log terakhir tentang Test Connection
$logContent = file_get_contents($logFile);
$lines = explode("\n", $logContent);
$testConnectionLogs = [];

foreach ($lines as $line) {
    if (strpos($line, 'Router test connection') !== false || 
        strpos($line, 'Connecting to MikroTik') !== false ||
        strpos($line, 'Connection successful') !== false ||
        strpos($line, 'Connection failed') !== false) {
        $testConnectionLogs[] = $line;
    }
}

echo "Recent Test Connection logs:\n";
echo "=" . str_repeat("=", 50) . "\n";
foreach (array_slice($testConnectionLogs, -20) as $log) {
    echo $log . "\n";
}

echo "\n\n=== TESTING DIFFERENT PASSWORD SCENARIOS ===\n\n";

// Get first router
$router = Router::first();
if (!$router) {
    echo "No router found in database\n";
    exit;
}

echo "Router details:\n";
echo "IP: {$router->ip_address}\n";
echo "Username: {$router->username}\n";
echo "Encrypted password length: " . strlen($router->password) . "\n";

try {
    $decryptedPassword = \Illuminate\Support\Facades\Crypt::decryptString($router->password);
    echo "Decrypted password length: " . strlen($decryptedPassword) . "\n";
    echo "Decrypted password is empty: " . (empty($decryptedPassword) ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "Failed to decrypt password: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING VARIOUS PASSWORD VALUES ===\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

// Test 1: Empty string
echo "\n1. Testing with empty string password:\n";
try {
    $result = $mikrotikService->connect($router->ip_address, $router->username, '', $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

// Test 2: Null password
echo "\n2. Testing with null password:\n";
try {
    $result = $mikrotikService->connect($router->ip_address, $router->username, null, $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

// Test 3: Decrypted password
echo "\n3. Testing with decrypted password:\n";
try {
    $decryptedPassword = \Illuminate\Support\Facades\Crypt::decryptString($router->password);
    $result = $mikrotikService->connect($router->ip_address, $router->username, $decryptedPassword, $router->port);
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

echo "\n=== SIMULATING EXACT FORM DATA ===\n";

// Simulate what the form sends
$formData = [
    'ip_address' => $router->ip_address,
    'username' => $router->username,
    'password' => '', // Form sends empty string when no password
    'port' => $router->port
];

echo "Form data simulation:\n";
foreach ($formData as $key => $value) {
    echo "  $key: '" . $value . "' (length: " . strlen($value ?? '') . ")\n";
}

echo "\n4. Testing with exact form data:\n";
try {
    $result = $mikrotikService->connect(
        $formData['ip_address'], 
        $formData['username'], 
        $formData['password'], 
        $formData['port']
    );
    echo "   Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
}

echo "\nAnalysis complete.\n";
