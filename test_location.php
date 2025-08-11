<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING LOCATION ENDPOINTS ===\n";

try {
    // Test Province 11 (DKI Jakarta)
    $cities = \Laravolt\Indonesia\Models\City::where('province_id', '11')
                ->orderBy('name')
                ->get(['id', 'name']);
    
    echo "Cities for Province 11 (DKI Jakarta): " . $cities->count() . "\n";
    
    if ($cities->count() > 0) {
        echo "First city: " . $cities->first()->name . "\n";
        
        // Test districts for first city
        $districts = \Laravolt\Indonesia\Models\District::where('city_id', $cities->first()->id)
                       ->orderBy('name')
                       ->get(['id', 'name']);
        
        echo "Districts for " . $cities->first()->name . ": " . $districts->count() . "\n";
        
        if ($districts->count() > 0) {
            echo "First district: " . $districts->first()->name . "\n";
            
            // Test villages for first district
            $villages = \Laravolt\Indonesia\Models\Village::where('district_id', $districts->first()->id)
                          ->orderBy('name')
                          ->get(['id', 'name']);
            
            echo "Villages for " . $districts->first()->name . ": " . $villages->count() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
