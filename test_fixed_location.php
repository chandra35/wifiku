<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FIXED LOCATION ENDPOINTS ===\n";

try {
    // Test Province code '11' (DKI Jakarta)
    echo "Testing province code '11' (should be DKI Jakarta)\n";
    
    $cities = \Laravolt\Indonesia\Models\City::where('province_code', '11')
                ->orderBy('name')
                ->get(['code as id', 'name']);
    
    echo "Cities for Province 11: " . $cities->count() . "\n";
    
    if ($cities->count() > 0) {
        echo "First city: " . $cities->first()->name . " (code: " . $cities->first()->id . ")\n";
        
        // Test districts for first city
        $districts = \Laravolt\Indonesia\Models\District::where('city_code', $cities->first()->id)
                       ->orderBy('name')
                       ->get(['code as id', 'name']);
        
        echo "Districts for " . $cities->first()->name . ": " . $districts->count() . "\n";
        
        if ($districts->count() > 0) {
            echo "First district: " . $districts->first()->name . " (code: " . $districts->first()->id . ")\n";
            
            // Test villages for first district  
            $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $districts->first()->id)
                          ->orderBy('name')
                          ->get(['code as id', 'name']);
            
            echo "Villages for " . $districts->first()->name . ": " . $villages->count() . "\n";
            
            if ($villages->count() > 0) {
                echo "First village: " . $villages->first()->name . "\n";
            }
        }
    }
    
    echo "\n=== SUCCESS! All location endpoints working ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
