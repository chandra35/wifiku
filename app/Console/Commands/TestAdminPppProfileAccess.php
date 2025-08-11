<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\PppProfile;
use App\Models\User;
use App\Http\Controllers\PackageController;
use Illuminate\Http\Request;

class TestAdminPppProfileAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin-ppp-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test admin access to PPP Profiles in packages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Admin PPP Profile Access...');
        
        // Get test data
        $router = Router::first();
        $superAdmin = User::whereHas('role', function($q) {
            $q->where('name', 'super_admin');
        })->first();
        
        $admin = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->first();
        
        if (!$router || !$superAdmin || !$admin) {
            $this->error('Test data not found. Please run seeders first.');
            return;
        }
        
        $this->info("Router: {$router->name} (ID: {$router->id})");
        $this->info("Super Admin: {$superAdmin->name}");
        $this->info("Admin: {$admin->name}");
        
        // Show all PPP Profiles for this router
        $allProfiles = PppProfile::where('router_id', $router->id)->get();
        $this->info("\nAll PPP Profiles for {$router->name}:");
        foreach ($allProfiles as $profile) {
            $creator = User::find($profile->created_by);
            $this->info("- {$profile->name} (Created by: {$creator->name} - {$creator->role->name})");
        }
        
        // Test as Super Admin
        $this->info("\n=== Testing as Super Admin ===");
        auth()->login($superAdmin);
        $request = new Request(['router_id' => $router->id]);
        $controller = new PackageController();
        $response = $controller->getPppProfiles($request);
        $data = json_decode($response->getContent(), true);
        $this->info("Super Admin sees " . count($data) . " profiles:");
        foreach ($data as $profile) {
            $this->info("- {$profile['name']}");
        }
        
        // Test as Admin
        $this->info("\n=== Testing as Admin ===");
        auth()->login($admin);
        $request2 = new Request(['router_id' => $router->id]);
        $response2 = $controller->getPppProfiles($request2);
        $data2 = json_decode($response2->getContent(), true);
        $this->info("Admin sees " . count($data2) . " profiles:");
        foreach ($data2 as $profile) {
            $this->info("- {$profile['name']}");
        }
        
        // Summary
        $this->info("\n=== Summary ===");
        $this->info("Super Admin should see ALL profiles for the router");
        $this->info("Admin should ONLY see profiles they created themselves");
        
        if (count($data2) < count($data)) {
            $this->info("✅ Filtering is working correctly!");
        } else {
            $this->error("❌ Filtering is NOT working - Admin sees too many profiles!");
        }
    }
}
