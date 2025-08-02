<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Finding correct MikroTik credentials...\n\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "❌ No router found in database!\n";
        exit(1);
    }
    
    echo "Router: {$router->name} ({$router->ip_address}:{$router->port})\n";
    echo "Current database: username='{$router->username}', password='admin'\n\n";
    
    $mikrotikService = new MikrotikService();
    
    // Common MikroTik username/password combinations
    $combinations = [
        ['admin', ''],           // Default MikroTik
        ['admin', 'admin'],      
        ['admin', 'password'],   
        ['admin', '123456'],
        ['admin', 'mikrotik'],
        ['web', ''],             // Current username with different passwords
        ['web', 'admin'],        // Current combo
        ['web', 'password'],     
        ['web', 'web'],
        ['web', '123456'],
        ['user', ''],
        ['user', 'user'],
        ['', ''],                // No username/password
    ];
    
    echo "Testing combinations...\n";
    
    foreach ($combinations as $i => $combo) {
        list($username, $password) = $combo;
        $displayUsername = $username ?: '(empty)';
        $displayPassword = $password ?: '(empty)';
        
        echo sprintf("%2d. Testing: %-10s / %-10s ... ", $i + 1, $displayUsername, $displayPassword);
        
        $result = $mikrotikService->testConnection(
            $router->ip_address,
            $username,
            $password,
            $router->port
        );
        
        if ($result['success']) {
            echo "✅ SUCCESS!\n\n";
            echo "🎉 Found working credentials:\n";
            echo "   Username: '$username'\n";
            echo "   Password: '$password'\n\n";
            
            // Update in database
            $encryptedPassword = Crypt::encryptString($password);
            DB::table('routers')
                ->where('id', $router->id)
                ->update([
                    'username' => $username,
                    'password' => $encryptedPassword
                ]);
            
            echo "✅ Credentials updated in database!\n";
            echo "\nNow try testing import/sync functions again.\n";
            exit(0);
        } else {
            echo "❌\n";
        }
    }
    
    echo "\n❌ None of the common combinations worked.\n";
    echo "Please check your MikroTik router configuration.\n";
    echo "You may need to:\n";
    echo "1. Enable API service in MikroTik\n";
    echo "2. Create a user with API access\n";
    echo "3. Check if firewall allows API connections\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
