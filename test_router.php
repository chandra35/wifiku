<?php

require_once 'vendor/autoload.php';

use App\Services\MikrotikService;

$service = new MikrotikService();

// Test different passwords
$passwords = ['web', 'admin', '', 'password', '123456'];

foreach ($passwords as $password) {
    echo "Testing password: '$password'\n";
    $result = $service->testConnection('192.168.88.1', 'web', $password, 8728);
    echo "Result: " . json_encode($result) . "\n\n";
    
    if ($result['success']) {
        echo "SUCCESS! Password is: '$password'\n";
        break;
    }
}
