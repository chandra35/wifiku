<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing exact same method as Test Connection form...\n\n";

try {
    // Get the router
    $router = DB::table('routers')->first();
    
    // Test with exact same data as form would send
    $testData = [
        'ip_address' => $router->ip_address,
        'username' => 'web',
        'password' => '', // Empty string like form sends
        'port' => $router->port
    ];
    
    echo "Testing with data exactly like form:\n";
    foreach ($testData as $key => $value) {
        $displayValue = $value === '' ? '(empty string)' : $value;
        echo "  $key: $displayValue\n";
    }
    echo "\n";
    
    // Use the exact same MikrotikService method as RouterController
    $mikrotikService = new MikrotikService();
    
    // This is exactly the same call as in RouterController::testConnection
    $result = $mikrotikService->testConnection(
        $testData['ip_address'],
        $testData['username'],
        $testData['password'],
        $testData['port']
    );
    
    echo "Result:\n";
    echo "  Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "  Message: " . $result['message'] . "\n";
    
    if ($result['success']) {
        echo "\n✅ SUCCESS! The credentials work!\n";
        echo "Updating database with correct credentials...\n";
        
        // Update database with correct credentials
        $encryptedPassword = Crypt::encryptString(''); // Empty password
        DB::table('routers')
            ->where('id', $router->id)
            ->update([
                'username' => 'web',
                'password' => $encryptedPassword
            ]);
        
        echo "✅ Database updated!\n";
        echo "\nNow test the import/sync functions.\n";
        
    } else {
        echo "\n❌ Still failing with exact same parameters.\n";
        echo "There might be something different in the environment.\n";
        
        // Let's try a few variations
        echo "\nTrying variations:\n";
        
        $variations = [
            ['web', null],      // null instead of empty string
            ['web', false],     // false instead of empty string
            ['web', 0],         // zero instead of empty string
        ];
        
        foreach ($variations as $i => $variation) {
            list($username, $password) = $variation;
            echo "  " . ($i + 1) . ". Username: '$username', Password: " . var_export($password, true) . " ... ";
            
            $result = $mikrotikService->testConnection(
                $router->ip_address,
                $username,
                $password,
                $router->port
            );
            
            if ($result['success']) {
                echo "✅ SUCCESS!\n";
                echo "    Found working combination!\n";
                break;
            } else {
                echo "❌\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDone!\n";
