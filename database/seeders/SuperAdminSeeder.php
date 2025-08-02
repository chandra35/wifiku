<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
        
        if ($superAdminRole) {
            \App\Models\User::firstOrCreate(
                ['email' => 'admin@wifiku.com'],
                [
                    'name' => 'Super Administrator',
                    'email' => 'admin@wifiku.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role_id' => $superAdminRole->id,
                    'email_verified_at' => now()
                ]
            );
        }
    }
}
