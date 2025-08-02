<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Debugging Router Password Issue...\n\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "❌ No router found in database!\n";
        exit(1);
    }
    
    echo "Current Router Info:\n";
    echo "- Name: {$router->name}\n";
    echo "- IP: {$router->ip_address}\n";
    echo "- Username: {$router->username}\n";
    echo "- Port: {$router->port}\n";
    echo "- Encrypted Password: " . substr($router->password, 0, 50) . "...\n\n";
    
    // Try to decrypt current password
    try {
        $currentPassword = Crypt::decryptString($router->password);
        echo "✅ Current password decrypted successfully\n";
        echo "Current password: '$currentPassword'\n";
    } catch (Exception $e) {
        echo "❌ Failed to decrypt current password: " . $e->getMessage() . "\n";
        echo "This means the password in database is corrupted or using wrong encryption key.\n\n";
        
        // Ask for correct password
        echo "Please enter the correct MikroTik password to fix this:\n";
        echo "Run: php update_router_credentials.php {$router->username} CORRECT_PASSWORD\n";
        exit(1);
    }
    
    // Test current credentials
    echo "\nTesting current credentials with testConnection method...\n";
    $mikrotikService = new MikrotikService();
    
    $testResult = $mikrotikService->testConnection(
        $router->ip_address,
        $router->username,
        $currentPassword,
        $router->port
    );
    
    if ($testResult['success']) {
        echo "✅ Current credentials work with testConnection!\n";
        echo "This means the problem is elsewhere.\n\n";
        
        // Test with direct connect
        echo "Testing with direct connect method...\n";
        $connectResult = $mikrotikService->connect(
            $router->ip_address,
            $router->username,
            $currentPassword,
            $router->port
        );
        
        if ($connectResult) {
            echo "✅ Direct connect also works!\n";
            echo "The credentials are correct. Problem might be in the trait or other code.\n";
        } else {
            echo "❌ Direct connect fails!\n";
            echo "There might be an issue with the MikrotikService connect method.\n";
        }
        
    } else {
        echo "❌ Current credentials don't work!\n";
        echo "Error: " . $testResult['message'] . "\n";
        echo "The password in database is wrong.\n\n";
        
        // Try common passwords
        echo "Trying common MikroTik passwords...\n";
        $commonPasswords = ['', 'admin', 'password', '123456'];
        
        foreach ($commonPasswords as $testPassword) {
            echo "Trying password: '" . ($testPassword ?: '(empty)') . "'... ";
            $result = $mikrotikService->testConnection(
                $router->ip_address,
                $router->username,
                $testPassword,
                $router->port
            );
            
            if ($result['success']) {
                echo "✅ WORKS!\n";
                echo "Found working password: '$testPassword'\n";
                
                // Update in database
                $encryptedPassword = Crypt::encryptString($testPassword);
                DB::table('routers')
                    ->where('id', $router->id)
                    ->update(['password' => $encryptedPassword]);
                
                echo "✅ Password updated in database!\n";
                break;
            } else {
                echo "❌ Failed\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
