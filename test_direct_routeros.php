<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use App\Models\Router;

echo "=== TESTING DIRECT ROUTEROS CLIENT ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Testing exact same method as testConnection...\n\n";

// Test credentials yang sama seperti log yang berhasil
$host = $router->ip_address;
$username = 'web';  // dari log yang berhasil
$password = '';     // dari log yang berhasil
$port = $router->port;

echo "Testing with:\n";
echo "Host: $host\n";
echo "Username: $username\n";
echo "Password: '$password' (length: " . strlen($password) . ")\n";
echo "Port: $port\n\n";

try {
    echo "Creating config...\n";
    $config = (new Config())
        ->set('host', $host)
        ->set('user', $username)
        ->set('pass', $password)
        ->set('port', (int)$port)
        ->set('timeout', 5);
    
    echo "Creating client...\n";
    $client = new Client($config);
    
    echo "Sending test query...\n";
    $query = new Query('/system/identity/print');
    $response = $client->query($query)->read();
    
    echo "SUCCESS! Response:\n";
    print_r($response);
    
} catch (Exception $e) {
    echo "FAILED! Exception: " . $e->getMessage() . "\n";
    echo "Exception class: " . get_class($e) . "\n";
    
    if (strpos($e->getMessage(), 'bad user name or password') !== false) {
        echo "\nThis is a credential issue.\n";
        echo "Let's try different combinations...\n\n";
        
        $combinations = [
            ['admin', ''],
            ['admin', 'admin'],
            ['web', 'admin'],
            ['api', ''],
            ['api', 'admin']
        ];
        
        foreach ($combinations as $i => $combo) {
            $testUser = $combo[0];
            $testPass = $combo[1];
            
            echo "Test " . ($i + 1) . ": username='$testUser', password='$testPass'\n";
            
            try {
                $testConfig = (new Config())
                    ->set('host', $host)
                    ->set('user', $testUser)
                    ->set('pass', $testPass)
                    ->set('port', (int)$port)
                    ->set('timeout', 5);
                
                $testClient = new Client($testConfig);
                $testQuery = new Query('/system/identity/print');
                $testResponse = $testClient->query($testQuery)->read();
                
                echo "   SUCCESS with $testUser / $testPass!\n";
                echo "   Response: " . json_encode($testResponse) . "\n\n";
                break;
                
            } catch (Exception $testE) {
                echo "   Failed: " . $testE->getMessage() . "\n";
            }
        }
    }
}

echo "\nTest complete.\n";
