<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\Router;
use App\Models\PppProfile;
use App\Models\User;

class TestPackageCreate extends Command
{
    protected $signature = 'test:package-create';
    protected $description = 'Test creating package without mikrotik_profile_name';

    public function handle()
    {
        // Get test data
        $admin = User::where('email', 'admin@test.com')->first();
        $router = Router::where('name', 'Router Jakarta')->first();
        $pppProfile = PppProfile::where('name', 'Profile 20M')->first();

        if (!$admin || !$router || !$pppProfile) {
            $this->error('Required test data not found (admin@test.com, Router Jakarta, Profile 20M)');
            return;
        }

        $this->info("Testing package creation without mikrotik_profile_name field...");

        try {
            $package = Package::create([
                'name' => 'Test Package - No MikroTik Profile Name',
                'description' => 'Testing package creation without mikrotik_profile_name',
                'router_id' => $router->id,
                'ppp_profile_id' => $pppProfile->id,
                'rate_limit' => '20M/20M',
                'burst_limit' => '40M/40M',
                'burst_threshold' => '20M/20M',
                'burst_time' => '8/8',
                'price' => 200000,
                'price_before_tax' => 180000,
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            $this->info("✅ Package created successfully!");
            $this->info("Package ID: {$package->id}");
            $this->info("Package Name: {$package->name}");
            $this->info("Price: {$package->price}");
            $this->info("Price Before Tax: {$package->price_before_tax}");
            $this->info("Rate Limit: {$package->rate_limit}");
            $this->info("PPP Profile: {$package->pppProfile->name}");
            $this->info("PPP Profile Rate Limit: {$package->pppProfile->rate_limit}");
            $this->info("Router: {$package->router->name}");

            // Clean up
            $package->delete();
            $this->info("✅ Test package cleaned up");

            $this->info("🎉 Test completed successfully! Package creation works without mikrotik_profile_name");

        } catch (\Exception $e) {
            $this->error("❌ Error creating package: " . $e->getMessage());
        }
    }
}
