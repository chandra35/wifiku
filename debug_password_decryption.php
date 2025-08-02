<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

echo "=== DEBUGGING PASSWORD DECRYPTION ISSUE ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Router details:\n";
echo "ID: {$router->id}\n";
echo "Name: {$router->name}\n";
echo "IP: {$router->ip_address}\n";
echo "Username: {$router->username}\n";
echo "Port: {$router->port}\n";

echo "\n=== PASSWORD ANALYSIS ===\n";
echo "Encrypted password in database:\n";
echo $router->password . "\n";
echo "Length: " . strlen($router->password) . " characters\n\n";

// Test decryption
echo "Testing password decryption:\n";
try {
    $decryptedPassword = Crypt::decryptString($router->password);
    echo "✓ Decryption successful\n";
    echo "Decrypted password: '$decryptedPassword'\n";
    echo "Length: " . strlen($decryptedPassword) . " characters\n";
    echo "Is empty: " . (empty($decryptedPassword) ? 'YES' : 'NO') . "\n";
    echo "Equals empty string: " . ($decryptedPassword === '' ? 'YES' : 'NO') . "\n";
    
    // Test the trait method
    echo "\n=== TESTING TRAIT METHOD ===\n";
    
    // Create temporary class to test trait
    $testClass = new class {
        use \App\Traits\HandlesMikrotikConnection;
        
        protected $mikrotikService;
        
        public function __construct()
        {
            $this->mikrotikService = app(\App\Services\MikrotikService::class);
        }
        
        public function testDecryption($router)
        {
            return $this->getDecryptedRouterPassword($router);
        }
        
        public function testConnection($router)
        {
            return $this->connectToMikrotik($router);
        }
    };
    
    echo "Testing trait getDecryptedRouterPassword():\n";
    $traitPassword = $testClass->testDecryption($router);
    echo "Result: '$traitPassword'\n";
    echo "Is null: " . (is_null($traitPassword) ? 'YES' : 'NO') . "\n";
    
    echo "\nTesting trait connectToMikrotik():\n";
    $traitConnection = $testClass->testConnection($router);
    echo "Connection result: " . ($traitConnection ? 'SUCCESS' : 'FAILED') . "\n";
    
} catch (Exception $e) {
    echo "✗ Decryption failed: " . $e->getMessage() . "\n";
    echo "Exception class: " . get_class($e) . "\n";
}

echo "\n=== TESTING DIRECT MIKROTIK CONNECTION ===\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

try {
    $password = Crypt::decryptString($router->password);
    echo "Testing direct connection with decrypted password...\n";
    
    $result = $mikrotikService->testConnection(
        $router->ip_address,
        $router->username,
        $password,
        $router->port
    );
    
    echo "Test connection result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Connection test failed: " . $e->getMessage() . "\n";
}

echo "\n=== LOG ANALYSIS ===\n";
echo "Recent logs from storage/logs/laravel.log:\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    // Get last 20 lines that contain MikroTik or password related logs
    $relevantLogs = [];
    foreach ($lines as $line) {
        if (strpos($line, 'MikroTik') !== false || 
            strpos($line, 'password') !== false ||
            strpos($line, 'login failure') !== false ||
            strpos($line, 'decrypt') !== false) {
            $relevantLogs[] = $line;
        }
    }
    
    echo "Recent relevant logs:\n";
    foreach (array_slice($relevantLogs, -10) as $log) {
        echo $log . "\n";
    }
} else {
    echo "Log file not found\n";
}

echo "\nAnalysis complete.\n";
