<?php

// Test Area Controller functionality
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing Area functionality...\n\n";
    
    // Test provinces count
    $provincesCount = \Laravolt\Indonesia\Models\Province::count();
    echo "1. Provinces count: {$provincesCount}\n";
    
    // Test cities count
    $citiesCount = \Laravolt\Indonesia\Models\City::count();
    echo "2. Cities count: {$citiesCount}\n";
    
    // Test districts count
    $districtsCount = \Laravolt\Indonesia\Models\District::count();
    echo "3. Districts count: {$districtsCount}\n";
    
    // Test villages count
    $villagesCount = \Laravolt\Indonesia\Models\Village::count();
    echo "4. Villages count: {$villagesCount}\n";
    
    // Test Area Controller
    $areaController = new \App\Http\Controllers\AreaController();
    
    echo "\n5. Testing AreaController methods:\n";
    
    // Test getProvinces
    try {
        $provincesResponse = $areaController->getProvinces();
        $provincesData = json_decode($provincesResponse->getContent(), true);
        echo "   - getProvinces(): " . (is_array($provincesData) ? count($provincesData) . " provinces returned" : "Error - Invalid response format") . "\n";
        
        // Test getCities for first province
        if (!empty($provincesData)) {
            $firstProvinceId = $provincesData[0]['id'];
            $citiesResponse = $areaController->getCities($firstProvinceId);
            $citiesData = json_decode($citiesResponse->getContent(), true);
            echo "   - getCities({$firstProvinceId}): " . (is_array($citiesData) ? count($citiesData) . " cities returned" : "Error") . "\n";
            
            // Test getDistricts for first city
            if (!empty($citiesData)) {
                $firstCityId = $citiesData[0]['id'];
                $districtsResponse = $areaController->getDistricts($firstCityId);
                $districtsData = json_decode($districtsResponse->getContent(), true);
                echo "   - getDistricts({$firstCityId}): " . (is_array($districtsData) ? count($districtsData) . " districts returned" : "Error") . "\n";
                
                // Test getVillages for first district
                if (!empty($districtsData)) {
                    $firstDistrictId = $districtsData[0]['id'];
                    $villagesResponse = $areaController->getVillages($firstDistrictId);
                    $villagesData = json_decode($villagesResponse->getContent(), true);
                    echo "   - getVillages({$firstDistrictId}): " . (is_array($villagesData) ? count($villagesData) . " villages returned" : "Error") . "\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "   - getProvinces(): Error - " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ All tests completed successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
