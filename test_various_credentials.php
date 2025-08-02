<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;

echo "=== TESTING VARIOUS MIKROTIK CREDENTIALS ===\n\n";

$router = Router::first();
$mikrotikService = app(\App\Services\MikrotikService::class);

echo "Testing different username/password combinations for MikroTik:\n";
echo "Router IP: {$router->ip_address}:{$router->port}\n\n";

// List of common MikroTik default credentials
$credentials = [
    ['admin', ''],              // admin with no password
    ['admin', 'admin'],         // admin with admin password  
    ['web', ''],                // web with no password (what we think is correct)
    ['web', 'web'],             // web with web password
    ['web', 'webweb'],          // web with webweb password (common)
    ['web', 'admin'],           // web with admin password
    ['api', ''],                // api with no password
    ['api', 'api'],             // api with api password
    ['', ''],                   // no username, no password
];

foreach ($credentials as $i => $cred) {
    $username = $cred[0];
    $password = $cred[1];
    
    echo sprintf("Test %d: username='%s', password='%s'\n", $i + 1, $username, $password);
    
    try {
        $result = $mikrotikService->testConnection(
            $router->ip_address,
            $username,
            $password,
            $router->port
        );
        
        if ($result['success']) {
            echo "   ✓ SUCCESS! This is the correct credential.\n";
            echo "   Response: " . json_encode($result['data']) . "\n";
            
            // Update database with correct credentials
            echo "\n   Updating database with correct credentials...\n";
            $router->username = $username;
            $router->password = \Illuminate\Support\Facades\Crypt::encryptString($password);
            $router->save();
            echo "   Database updated!\n\n";
            break;
        } else {
            echo "   ✗ Failed: " . $result['message'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Exception: " . $e->getMessage() . "\n";
    }
}

echo "\n=== TESTING FROM DIFFERENT NETWORK CONTEXT ===\n";
echo "Note: Jika semua credential gagal dari CLI, kemungkinan:\n";
echo "1. MikroTik hanya allow koneksi dari web server (bukan CLI)\n";
echo "2. Ada firewall rule yang berbeda untuk web vs CLI\n";
echo "3. Ada binding interface yang spesifik\n";
echo "4. Credential di MikroTik baru saja berubah\n\n";

echo "Recommendation:\n";
echo "1. Coba Test Connection dari web browser\n";
echo "2. Periksa /ip service di MikroTik\n";
echo "3. Periksa /user di MikroTik\n";
echo "4. Periksa firewall rules di MikroTik\n";

echo "\nAnalysis complete.\n";
