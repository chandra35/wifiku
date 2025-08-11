<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\User;

class TestPackageRoleAccess extends Command
{
    protected $signature = 'test:package-role-access';
    protected $description = 'Test role-based access for packages';

    public function handle()
    {
        // Get test users
        $superAdmin = User::where('email', 'admin@wifiku.com')->first();
        $admin = User::where('email', 'admin@test.com')->first();

        if (!$superAdmin || !$admin) {
            $this->error('Required test users not found');
            return;
        }

        $this->info("Testing Package Role-Based Access...");

        // Get packages for each user
        $superAdminPackages = Package::when($superAdmin->role->name !== 'super_admin', function($query) use ($superAdmin) {
            return $query->where('created_by', $superAdmin->id);
        })->count();

        $adminPackages = Package::when($admin->role->name !== 'super_admin', function($query) use ($admin) {
            return $query->where('created_by', $admin->id);
        })->count();

        $totalPackages = Package::count();

        $this->info("Total packages in database: {$totalPackages}");
        $this->info("Super Admin sees: {$superAdminPackages} packages");
        $this->info("Admin sees: {$adminPackages} packages");

        // Test role filtering logic
        if ($superAdmin->role->name === 'super_admin') {
            $this->info("✅ Super Admin role check passed");
        } else {
            $this->error("❌ Super Admin role check failed");
        }

        if ($admin->role->name !== 'super_admin') {
            $this->info("✅ Admin role check passed (non super_admin)");
        } else {
            $this->error("❌ Admin role check failed");
        }

        $this->info("🎉 Role-based access test completed!");
    }
}
