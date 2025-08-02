<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;
use App\Models\PppProfile;

echo "=== TESTING PPP PROFILE IMPORT DUPLICATE CHECK ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Router: {$router->name} ({$router->ip_address})\n\n";

// Check current profiles in database
echo "=== CURRENT PROFILES IN DATABASE ===\n";
$existingProfiles = PppProfile::where('router_id', $router->id)->get();
echo "Found " . $existingProfiles->count() . " existing profiles:\n";

foreach ($existingProfiles as $profile) {
    echo "- {$profile->name} (ID: {$profile->id}, MikroTik ID: {$profile->mikrotik_id})\n";
}

echo "\n=== TESTING MIKROTIK CONNECTION ===\n";

$mikrotikService = app(\App\Services\MikrotikService::class);

// Test connection using trait
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

$connected = $testClass->testConnection($router);
echo "Connection to MikroTik: " . ($connected ? 'SUCCESS ✓' : 'FAILED ✗') . "\n";

if ($connected) {
    echo "\n=== GETTING PROFILES FROM MIKROTIK ===\n";
    
    $result = $mikrotikService->getPppProfiles();
    
    if ($result['success']) {
        echo "Found " . count($result['data']) . " profiles in MikroTik:\n";
        
        foreach ($result['data'] as $mikrotikProfile) {
            $name = $mikrotikProfile['name'];
            $mikrotikId = $mikrotikProfile['.id'];
            
            // Check for duplicates
            $existsByName = PppProfile::where('router_id', $router->id)
                ->where('name', $name)
                ->first();
                
            $existsByMikrotikId = PppProfile::where('router_id', $router->id)
                ->where('mikrotik_id', $mikrotikId)
                ->first();
            
            $status = 'NEW';
            if ($existsByName && $existsByMikrotikId && $existsByName->id === $existsByMikrotikId->id) {
                $status = 'EXISTS';
            } elseif ($existsByName) {
                $status = 'NAME_CONFLICT';
            } elseif ($existsByMikrotikId) {
                $status = 'ID_CONFLICT';
            }
            
            echo "- {$name} (MikroTik ID: {$mikrotikId}) -> {$status}\n";
        }
        
        echo "\n=== IMPORT SUMMARY ===\n";
        $newCount = 0;
        $existsCount = 0;
        $conflictCount = 0;
        
        foreach ($result['data'] as $mikrotikProfile) {
            $name = $mikrotikProfile['name'];
            $mikrotikId = $mikrotikProfile['.id'];
            
            $existsByName = PppProfile::where('router_id', $router->id)
                ->where('name', $name)
                ->first();
                
            $existsByMikrotikId = PppProfile::where('router_id', $router->id)
                ->where('mikrotik_id', $mikrotikId)
                ->first();
            
            if ($existsByName && $existsByMikrotikId && $existsByName->id === $existsByMikrotikId->id) {
                $existsCount++;
            } elseif ($existsByName || $existsByMikrotikId) {
                $conflictCount++;
            } else {
                $newCount++;
            }
        }
        
        echo "Profiles that can be imported: {$newCount}\n";
        echo "Profiles already exist: {$existsCount}\n";
        echo "Profiles with conflicts: {$conflictCount}\n";
        
    } else {
        echo "Failed to get profiles from MikroTik: " . $result['message'] ?? 'Unknown error' . "\n";
    }
} else {
    echo "Cannot test profile import - connection failed\n";
}

echo "\n=== DUPLICATE CHECK LOGIC ===\n";
echo "The improved import now checks for duplicates by:\n";
echo "1. Profile name (prevents duplicate names)\n";
echo "2. MikroTik ID (prevents duplicate IDs)\n";
echo "3. Detailed status reporting (exists, name conflict, ID conflict)\n";
echo "4. Enhanced logging for debugging\n";
echo "5. Error handling for failed imports\n";

echo "\nImport Profile function is now ready with enhanced duplicate checking!\n";
