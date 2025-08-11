<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PppProfile;
use App\Models\Router;
use App\Models\User;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $routers = Router::all();
        $pppProfiles = PppProfile::all();
        $users = User::all();

        if ($routers->isEmpty() || $pppProfiles->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please run TestDataSeeder first to create routers, profiles, and users.');
            return;
        }

        // Get a super admin user for created_by
        $superAdmin = User::whereHas('role', function($q) {
            $q->where('name', 'super_admin');
        })->first();

        $admin = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->first();

        // Create sample packages
        $packages = [
            [
                'name' => 'Paket Basic 10MB',
                'price' => 100000, // Price in IDR
                'description' => 'Paket internet basic speed 10 Mbps',
                'rate_limit' => '10M/10M',
                'ppp_profile_id' => $pppProfiles->first()->id,
                'router_id' => $routers->first()->id,
                'created_by' => $superAdmin->id,
                'is_active' => true
            ],
            [
                'name' => 'Paket Standard 20MB',
                'price' => 150000, // Price in IDR
                'description' => 'Paket internet standard speed 20 Mbps',
                'rate_limit' => '20M/20M',
                'ppp_profile_id' => $pppProfiles->skip(1)->first()->id ?? $pppProfiles->first()->id,
                'router_id' => $routers->first()->id,
                'created_by' => $superAdmin->id,
                'is_active' => true
            ],
            [
                'name' => 'Paket Premium 50MB',
                'price' => 300000, // Price in IDR
                'description' => 'Paket internet premium speed 50 Mbps',
                'rate_limit' => '50M/50M',
                'ppp_profile_id' => $pppProfiles->first()->id,
                'router_id' => $routers->first()->id,
                'created_by' => $admin->id ?? $superAdmin->id,
                'is_active' => true
            ]
        ];

        foreach ($packages as $packageData) {
            Package::firstOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );
        }

        $this->command->info('Package seeder completed!');
        $this->command->info('Created 3 sample packages with different price tiers.');
    }
}
