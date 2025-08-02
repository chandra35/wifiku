<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing MikroTik connection with current router credentials...\n\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "❌ No router found in database!\n";
        exit(1);
    }
    
    echo "Router Info:\n";
    echo "- Name: {$router->name}\n";
    echo "- IP: {$router->ip_address}\n";
    echo "- Username: {$router->username}\n";
    echo "- Port: {$router->port}\n\n";
    
    // Decrypt password
    try {
        $password = Crypt::decryptString($router->password);
        echo "✅ Password decrypted successfully\n";
    } catch (Exception $e) {
        echo "❌ Failed to decrypt password: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test connection
    echo "\nTesting connection...\n";
    
    $mikrotikService = new MikrotikService();
    $result = $mikrotikService->testConnection(
        $router->ip_address,
        $router->username,
        $password,
        $router->port
    );
    
    if ($result['success']) {
        echo "✅ Connection successful!\n";
        echo "Message: " . $result['message'] . "\n";
        
        // Try to get system info
        echo "\nTesting system resource retrieval...\n";
        $connected = $mikrotikService->connect(
            $router->ip_address,
            $router->username,
            $password,
            $router->port
        );
        
        if ($connected) {
            $systemResult = $mikrotikService->getSystemResource();
            if ($systemResult['success']) {
                echo "✅ System resource retrieval successful!\n";
                $data = $systemResult['data'];
                echo "- CPU Load: {$data['cpu_load']}%\n";
                echo "- Memory Usage: " . round($data['memory_usage_percent'], 1) . "%\n";
                echo "- Uptime: {$data['uptime']}\n";
                echo "- Version: {$data['version']}\n";
            } else {
                echo "❌ Failed to get system resource: " . $systemResult['message'] . "\n";
            }
        } else {
            echo "❌ Failed to establish connection for system check\n";
        }
        
    } else {
        echo "❌ Connection failed!\n";
        echo "Error: " . $result['message'] . "\n";
        echo "\nThis means the username/password is incorrect.\n";
        echo "Please run: php update_router_credentials.php CORRECT_USERNAME CORRECT_PASSWORD\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
