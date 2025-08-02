<?php

$file = 'd:\projek\wifiku\app\Http\Controllers\PppoeController.php';
$content = file_get_contents($file);

// Pattern to replace mikrotikService->connect calls
$pattern = '/\$connected = \$this->mikrotikService->connect\(\s*\$router->ip_address,\s*\$router->username,\s*\$router->password,\s*\$router->port\s*\);/';
$replacement = '$connected = $this->connectToMikrotik($router);';

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $content);

echo "All mikrotikService->connect calls have been replaced with connectToMikrotik calls.\n";
