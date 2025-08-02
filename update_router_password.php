<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get password from command line argument
if ($argc < 2) {
    echo "Usage: php update_router_password.php <password>\n";
    echo "Example: php update_router_password.php mypassword123\n";
    exit(1);
}

$newPassword = $argv[1];

echo "Updating router password...\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "No router found!\n";
        exit;
    }
    
    echo "Found router: {$router->name} ({$router->ip_address})\n";
    
    // Encrypt the new password
    $encryptedPassword = Crypt::encryptString($newPassword);
    
    // Update the router password
    DB::table('routers')
        ->where('id', $router->id)
        ->update(['password' => $encryptedPassword]);
    
    echo "Password updated successfully!\n";
    
    // Test decryption
    $decrypted = Crypt::decryptString($encryptedPassword);
    if ($decrypted === $newPassword) {
        echo "✓ Password encryption/decryption test passed\n";
    } else {
        echo "✗ Password encryption/decryption test failed\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done!\n";
