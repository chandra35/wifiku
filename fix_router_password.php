<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Fixing router password...\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    if (!$router) {
        echo "No router found!\n";
        exit;
    }
    
    echo "Found router: {$router->name} ({$router->ip_address})\n";
    echo "Current password (encrypted): " . $router->password . "\n";
    
    // Let's set a default password and encrypt it properly
    $newPassword = 'admin'; // Default MikroTik password
    $encryptedPassword = Crypt::encryptString($newPassword);
    
    // Update the router password
    DB::table('routers')
        ->where('id', $router->id)
        ->update(['password' => $encryptedPassword]);
    
    echo "Password updated to 'admin' and encrypted properly.\n";
    echo "New encrypted password: " . $encryptedPassword . "\n";
    
    // Test decryption
    $decrypted = Crypt::decryptString($encryptedPassword);
    echo "Decryption test: " . $decrypted . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done!\n";
