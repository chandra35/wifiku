<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get username and password from command line arguments
if ($argc < 3) {
    echo "Usage: php update_router_credentials.php <username> <password>\n";
    echo "Example: php update_router_credentials.php admin mypassword123\n";
    exit(1);
}

$newUsername = $argv[1];
$newPassword = $argv[2];

echo "Updating router credentials...\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "No router found!\n";
        exit;
    }
    
    echo "Found router: {$router->name} ({$router->ip_address})\n";
    echo "Current username: {$router->username}\n";
    
    // Encrypt the new password
    $encryptedPassword = Crypt::encryptString($newPassword);
    
    // Update the router credentials
    DB::table('routers')
        ->where('id', $router->id)
        ->update([
            'username' => $newUsername,
            'password' => $encryptedPassword
        ]);
    
    echo "✓ Username updated to: {$newUsername}\n";
    echo "✓ Password updated and encrypted successfully!\n";
    
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

echo "Done! Please try accessing the router management page again.\n";
