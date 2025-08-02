<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing with username 'web' and different passwords...\n\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    $mikrotikService = new MikrotikService();
    
    // Test with username 'web' and various passwords
    $passwords = [
        '',              // Empty password
        'web',           // Same as username
        'admin',         // Current in database
        'password',      // Common password
        '123456',        // Common password
        'mikrotik',      // Router brand
        'omah',          // Router name
        'Omah',          // Router name capitalized
    ];
    
    echo "Testing username 'web' with different passwords:\n";
    
    foreach ($passwords as $i => $password) {
        $displayPassword = $password === '' ? '(empty)' : "'$password'";
        echo sprintf("%d. Password: %-12s ... ", $i + 1, $displayPassword);
        
        $result = $mikrotikService->testConnection(
            $router->ip_address,
            'web',
            $password,
            $router->port
        );
        
        if ($result['success']) {
            echo "✅ SUCCESS!\n\n";
            echo "🎉 Found working credentials:\n";
            echo "   Username: 'web'\n";
            echo "   Password: '$password'\n\n";
            
            // Update in database
            $encryptedPassword = Crypt::encryptString($password);
            DB::table('routers')
                ->where('id', $router->id)
                ->update([
                    'username' => 'web',
                    'password' => $encryptedPassword
                ]);
            
            echo "✅ Password updated in database!\n";
            echo "\nNow test the import/sync functions again.\n";
            exit(0);
        } else {
            echo "❌\n";
        }
    }
    
    echo "\n❌ None of the passwords worked with username 'web'.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
