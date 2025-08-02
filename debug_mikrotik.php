<?php

require_once 'vendor/autoload.php';

use App\Services\MikrotikService;
use App\Models\Router;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Get router data - use first() instead of find(1)
    $router = Router::first();
    if (!$router) {
        echo "No router found\n";
        exit(1);
    }
    
    echo "Testing MikroTik connection to: " . $router->ip_address . "\n";
    echo "Username: " . $router->username . "\n";
    echo "Port: " . $router->port . "\n";
    
    // Decrypt password
    $password = decrypt($router->password);
    echo "Password decrypted: " . (strlen($password) > 0 ? 'Yes' : 'No') . "\n";
    
    // Test connection
    $service = new MikrotikService();
    $result = $service->testConnection($router->ip_address, $router->username, $password, $router->port);
    
    echo "Test Connection Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    if ($result['success']) {
        echo "\nTrying connect method:\n";
        $connected = $service->connect($router->ip_address, $router->username, $password, $router->port);
        echo "Connect result: " . ($connected ? 'Success' : 'Failed') . "\n";
        
        if ($connected) {
            echo "\nTrying to get system info:\n";
            $client = $service->getClient();
            
            // Get system identity
            $identity = $client->query('/system/identity/print')->read();
            echo "Identity: " . json_encode($identity, JSON_PRETTY_PRINT) . "\n";
            
            // Get system resource
            $resource = $client->query('/system/resource/print')->read();
            echo "Resource: " . json_encode($resource, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
