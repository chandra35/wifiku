<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;

// Test area functionality
Route::get('/debug-area', function() {
    try {
        $areaController = new AreaController();
        
        // Test provinces
        $provincesResponse = $areaController->getProvinces();
        $provinces = json_decode($provincesResponse->getContent(), true);
        
        $output = [
            'provinces_count' => count($provinces['data'] ?? []),
            'first_province' => $provinces['data'][0] ?? 'No provinces found',
        ];
        
        // Test cities if we have provinces
        if (!empty($provinces['data'])) {
            $firstProvinceId = $provinces['data'][0]['id'];
            $citiesResponse = $areaController->getCities($firstProvinceId);
            $cities = json_decode($citiesResponse->getContent(), true);
            
            $output['cities_count_for_first_province'] = count($cities['data'] ?? []);
            $output['first_city'] = $cities['data'][0] ?? 'No cities found';
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Area API is working!',
            'data' => $output
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error testing area API',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth');
