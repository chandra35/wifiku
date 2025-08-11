<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Router;
use App\Models\PppProfile;
use App\Models\User;

class FixPppProfileDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first router
        $router1 = Router::first();
        $router2 = Router::skip(1)->first();
        
        if (!$router1) {
            $this->command->error('No routers found. Please run TestDataSeeder first.');
            return;
        }

        // Get users
        $superAdmin = User::whereHas('role', function($q) {
            $q->where('name', 'super_admin');
        })->first();
        
        $admin = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->first();

        // Clear existing PPP Profiles (delete instead of truncate)
        PppProfile::query()->delete();

        // Create PPP Profiles for Router 1
        PppProfile::create([
            'name' => 'Profile 10M',
            'router_id' => $router1->id,
            'rate_limit' => '10M/10M',
            'created_by' => $superAdmin->id,
            'is_synced' => false
        ]);

        PppProfile::create([
            'name' => 'Profile 20M',
            'router_id' => $router1->id,
            'rate_limit' => '20M/20M',
            'created_by' => $admin->id,
            'is_synced' => false
        ]);

        // Create PPP Profiles for Router 2 (if exists)
        if ($router2) {
            PppProfile::create([
                'name' => 'Profile 50M',
                'router_id' => $router2->id,
                'rate_limit' => '50M/50M',
                'created_by' => $superAdmin->id,
                'is_synced' => false
            ]);
        }

        $this->command->info('PPP Profile data fixed successfully!');
        $this->command->info('Router 1 (' . $router1->name . '): 2 profiles');
        if ($router2) {
            $this->command->info('Router 2 (' . $router2->name . '): 1 profile');
        }
    }
}
