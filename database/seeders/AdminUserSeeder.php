<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@wifiku.com',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'email_verified_at' => now(),
        ]);

        // Create Admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@wifiku.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin users created successfully:');
        $this->command->info('Super Admin: superadmin@wifiku.com / password123');
        $this->command->info('Admin: admin@wifiku.com / password123');
    }
}
