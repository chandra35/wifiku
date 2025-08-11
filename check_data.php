<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DATA CHECK ===\n";
echo "Provinces: " . \Laravolt\Indonesia\Models\Province::count() . "\n";
echo "Cities: " . \Laravolt\Indonesia\Models\City::count() . "\n";
echo "Roles: " . \App\Models\Role::count() . "\n";

echo "\nRoles list:\n";
foreach (\App\Models\Role::all() as $role) {
    echo "- {$role->name}\n";
}

echo "\nUsers: " . \App\Models\User::count() . "\n";
echo "Packages: " . \App\Models\Package::count() . "\n";
echo "Customers: " . \App\Models\Customer::count() . "\n";
