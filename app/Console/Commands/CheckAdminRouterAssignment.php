<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\User;

class CheckAdminRouterAssignment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:admin-routers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check admin router assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Admin Router Assignments...');
        
        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->with('routers')->get();
        
        if ($admins->isEmpty()) {
            $this->error('No admin users found.');
            return;
        }
        
        foreach ($admins as $admin) {
            $this->info("\nAdmin: {$admin->name} ({$admin->email})");
            if ($admin->routers->isEmpty()) {
                $this->warn("  ❌ No routers assigned!");
            } else {
                $this->info("  ✅ Assigned routers:");
                foreach ($admin->routers as $router) {
                    $this->info("    - {$router->name} ({$router->ip_address})");
                }
            }
        }
        
        // Show all routers
        $this->info("\nAll Available Routers:");
        $routers = Router::all();
        foreach ($routers as $router) {
            $assignedUsers = $router->users()->count();
            $this->info("- {$router->name} ({$router->ip_address}) - Assigned to {$assignedUsers} user(s)");
        }
        
        // Auto-assign if needed
        $unassignedAdmin = $admins->filter(function($admin) {
            return $admin->routers->isEmpty();
        })->first();
        
        if ($unassignedAdmin && $routers->isNotEmpty()) {
            $this->info("\nAuto-assigning router to unassigned admin...");
            $firstRouter = $routers->first();
            $unassignedAdmin->routers()->attach($firstRouter->id);
            $this->info("✅ Assigned {$firstRouter->name} to {$unassignedAdmin->name}");
        }
    }
}
