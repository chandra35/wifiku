<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING LARAVOLT TABLE STRUCTURE ===\n";

try {
    // Check provinces table
    $provinces = \DB::select('DESCRIBE indonesia_provinces');
    echo "Provinces table columns:\n";
    foreach ($provinces as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nCities table columns:\n";
    $cities = \DB::select('DESCRIBE indonesia_cities');
    foreach ($cities as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nFirst 3 provinces:\n";
    $sampleProvinces = \DB::table('indonesia_provinces')->limit(3)->get();
    foreach ($sampleProvinces as $province) {
        echo "- ID: {$province->id}, Name: {$province->name}\n";
    }
    
    echo "\nFirst 3 cities:\n";
    $sampleCities = \DB::table('indonesia_cities')->limit(3)->get();
    foreach ($sampleCities as $city) {
        print_r($city);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
