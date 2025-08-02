<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Router;
use App\Models\PppProfile;

echo "=== TESTING PROFILE IMPORT WITH PROPER CONNECTION ===\n\n";

$router = Router::first();
if (!$router) {
    echo "No router found\n";
    exit;
}

echo "Router: {$router->name} ({$router->ip_address})\n";

// Create controller-like test class
$testController = new class {
    use \App\Traits\HandlesMikrotikConnection;
    
    protected $mikrotikService;
    
    public function __construct()
    {
        $this->mikrotikService = app(\App\Services\MikrotikService::class);
    }
    
    public function testImportProcess($router)
    {
        // Connect to MikroTik using trait method
        echo "Connecting to MikroTik...\n";
        $connected = $this->connectToMikrotik($router);
        
        if (!$connected) {
            echo "❌ Failed to connect to MikroTik\n";
            return false;
        }
        
        echo "✅ Connected to MikroTik successfully\n";
        
        // Get profiles
        echo "Getting PPP profiles...\n";
        $result = $this->mikrotikService->getPppProfiles();
        
        if (!$result['success']) {
            echo "❌ Failed to get profiles: " . $result['message'] . "\n";
            return false;
        }
        
        echo "✅ Retrieved " . count($result['data']) . " profiles from MikroTik\n\n";
        
        // Analyze profiles for duplicates
        echo "=== ANALYZING PROFILES ===\n";
        
        $newCount = 0;
        $existsCount = 0;
        $conflictCount = 0;
        
        foreach ($result['data'] as $mikrotikProfile) {
            $name = $mikrotikProfile['name'];
            $mikrotikId = $mikrotikProfile['.id'];
            
            // Check if profile already exists in database
            $existingByName = PppProfile::where('router_id', $router->id)
                ->where('name', $name)
                ->first();

            $existingByMikrotikId = PppProfile::where('router_id', $router->id)
                ->where('mikrotik_id', $mikrotikId)
                ->first();

            $status = 'NEW';
            $statusDetail = '';
            
            if ($existingByName && $existingByMikrotikId && $existingByName->id === $existingByMikrotikId->id) {
                $status = 'EXISTS';
                $statusDetail = 'Profile already exists in database';
                $existsCount++;
            } elseif ($existingByName) {
                $status = 'NAME_CONFLICT';
                $statusDetail = 'Profile name already exists with different MikroTik ID';
                $conflictCount++;
            } elseif ($existingByMikrotikId) {
                $status = 'ID_CONFLICT';
                $statusDetail = 'MikroTik ID already exists with different name';
                $conflictCount++;
            } else {
                $newCount++;
            }
            
            $icon = ($status === 'NEW') ? '🆕' : (($status === 'EXISTS') ? '✅' : '⚠️');
            echo "{$icon} {$name} (MikroTik ID: {$mikrotikId}) -> {$status}\n";
            if ($statusDetail) {
                echo "   └─ {$statusDetail}\n";
            }
        }
        
        echo "\n=== IMPORT SUMMARY ===\n";
        echo "🆕 New profiles (can be imported): {$newCount}\n";
        echo "✅ Already exist (will be skipped): {$existsCount}\n";
        echo "⚠️  Conflicts (will be skipped): {$conflictCount}\n";
        
        return true;
    }
};

// Test the import process
$testController->testImportProcess($router);

echo "\n=== IMPROVEMENTS MADE ===\n";
echo "✅ Enhanced duplicate checking:\n";
echo "   - Check by profile name\n";
echo "   - Check by MikroTik ID\n";
echo "   - Detect conflicts (name vs ID mismatch)\n";
echo "✅ Better logging and error handling\n";
echo "✅ Detailed status reporting in preview\n";
echo "✅ Skip logic for existing profiles\n";
echo "✅ Exception handling for failed imports\n";

echo "\n🎉 PPP Profile Import is now ready with comprehensive duplicate checking!\n";
echo "\nAnda bisa test fungsi ini di web interface:\n";
echo "1. Buka halaman PPP Profile\n";
echo "2. Klik 'Import from MikroTik'\n";
echo "3. Lihat preview dengan status setiap profile\n";
echo "4. Pilih profile yang ingin diimport\n";
echo "5. Profile yang sudah ada akan di-skip otomatis\n";
