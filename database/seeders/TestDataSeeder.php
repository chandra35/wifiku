<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Router;
use App\Models\PppProfile;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // Create or get super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => $superAdminRole->id
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id
            ]
        );

        // Create another admin user
        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@test.com'],
            [
                'name' => 'Admin User 2',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id
            ]
        );

        // Create test routers
        $router1 = Router::firstOrCreate(
            ['name' => 'Router Jakarta'],
            [
                'ip_address' => '192.168.1.1',
                'username' => 'admin',
                'password' => 'password',
                'port' => 8728,
                'status' => 'active'
            ]
        );

        $router2 = Router::firstOrCreate(
            ['name' => 'Router Bandung'],
            [
                'ip_address' => '192.168.2.1',
                'username' => 'admin',
                'password' => 'password',
                'port' => 8728,
                'status' => 'active'
            ]
        );

        // Assign routers to admin users
        if (!$admin->routers->contains($router1->id)) {
            $admin->routers()->attach($router1->id);
        }
        if (!$admin2->routers->contains($router2->id)) {
            $admin2->routers()->attach($router2->id);
        }

        // Create sample PPP Profiles
        PppProfile::firstOrCreate(
            ['name' => 'Profile Super Admin 1', 'router_id' => $router1->id],
            [
                'rate_limit' => '10M/10M',
                'created_by' => $superAdmin->id,
                'is_synced' => false
            ]
        );

        PppProfile::firstOrCreate(
            ['name' => 'Profile Super Admin 2', 'router_id' => $router2->id],
            [
                'rate_limit' => '20M/20M',
                'created_by' => $superAdmin->id,
                'is_synced' => false
            ]
        );

        PppProfile::firstOrCreate(
            ['name' => 'Profile Admin 1', 'router_id' => $router1->id],
            [
                'rate_limit' => '5M/5M',
                'created_by' => $admin->id,
                'is_synced' => false
            ]
        );

        PppProfile::firstOrCreate(
            ['name' => 'Profile Admin 2', 'router_id' => $router2->id],
            [
                'rate_limit' => '8M/8M',
                'created_by' => $admin2->id,
                'is_synced' => false
            ]
        );

        $this->command->info('Test data created successfully!');
        $this->command->info('Users available:');
        $this->command->info('- superadmin@example.com (password: password) - Super Admin');
        $this->command->info('- admin@test.com (password: password) - Admin (Router Jakarta)');
        $this->command->info('- admin2@test.com (password: password) - Admin (Router Bandung)');
    }
}
