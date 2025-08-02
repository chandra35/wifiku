<?php

$file = 'd:\projek\wifiku\app\Http\Controllers\PppProfileController.php';
$content = file_get_contents($file);

// Pattern to replace mikrotikService->connect calls
$patterns = [
    // Standard pattern with router
    '/\$connected = \$this->mikrotikService->connect\(\s*\$router->ip_address,\s*\$router->username,\s*\$router->password,\s*\$router->port\s*\);/',
    // Pattern with profile->router
    '/\$connected = \$this->mikrotikService->connect\(\s*\$profile->router->ip_address,\s*\$profile->router->username,\s*\$profile->router->password,\s*\$profile->router->port\s*\);/'
];

$replacements = [
    '$connected = $this->connectToMikrotik($router);',
    '$connected = $this->connectToMikrotik($profile->router);'
];

$content = preg_replace($patterns, $replacements, $content);

file_put_contents($file, $content);

echo "All mikrotikService->connect calls in PppProfileController have been replaced.\n";
