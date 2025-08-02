<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;

echo "=== TESTING CORRECTED CREDENTIALS ===\n\n";

$router = Router::first();

echo "Updated router credentials:\n";
echo "Username: {$router->username}\n";

try {
    $decryptedPassword = \Illuminate\Support\Facades\Crypt::decryptString($router->password);
    echo "Password: '$decryptedPassword'\n";
} catch (Exception $e) {
    echo "Failed to decrypt password: " . $e->getMessage() . "\n";
}

echo "IP: {$router->ip_address}\n";
echo "Port: {$router->port}\n\n";

// Test using trait method
echo "=== TESTING TRAIT CONNECTION ===\n";

$testClass = new class {
    use \App\Traits\HandlesMikrotikConnection;
    
    protected $mikrotikService;
    
    public function __construct()
    {
        $this->mikrotikService = app(\App\Services\MikrotikService::class);
    }
    
    public function testConnection($router)
    {
        return $this->connectToMikrotik($router);
    }
};

$traitResult = $testClass->testConnection($router);
echo "Trait connectToMikrotik() result: " . ($traitResult ? 'SUCCESS ✓' : 'FAILED ✗') . "\n";

// Test MikroTik service
echo "\n=== TESTING MIKROTIK SERVICE ===\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

try {
    $password = \Illuminate\Support\Facades\Crypt::decryptString($router->password);
    
    $result = $mikrotikService->testConnection(
        $router->ip_address,
        $router->username,
        $password,
        $router->port
    );
    
    echo "MikrotikService testConnection() result:\n";
    if ($result['success']) {
        echo "✓ SUCCESS\n";
        echo "Message: {$result['message']}\n";
        echo "Data: " . json_encode($result['data']) . "\n";
    } else {
        echo "✗ FAILED\n";
        echo "Message: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Kredensial MikroTik yang benar adalah:\n";
echo "- Username: web\n";
echo "- Password: webweb\n\n";

echo "Sekarang silakan:\n";
echo "1. Refresh halaman Router Management\n";
echo "2. Coba Import Secret dari MikroTik\n";
echo "3. Coba Import Profile dari MikroTik\n";
echo "4. Periksa status router (Connection, CPU, Memory, dll)\n";

echo "\nSemua fungsi seharusnya sudah berfungsi normal.\n";
