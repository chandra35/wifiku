<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\PppProfile;
use App\Models\User;
use App\Http\Controllers\PackageController;
use Illuminate\Http\Request;

class TestPppProfileEndpoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ppp-profiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PPP Profile endpoint functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PPP Profile Endpoint...');
        
        // Get test data
        $router = Router::first();
        $user = User::whereHas('role', function($q) {
            $q->where('name', 'super_admin');
        })->first();
        
        if (!$router || !$user) {
            $this->error('Test data not found. Please run seeders first.');
            return;
        }
        
        $this->info("Router: {$router->name} (ID: {$router->id})");
        
        // Count PPP Profiles for this router
        $profileCount = PppProfile::where('router_id', $router->id)->count();
        $this->info("PPP Profiles for this router: {$profileCount}");
        
        // Simulate login
        auth()->login($user);
        $this->info("Logged in as: {$user->name} ({$user->role->name})");
        
        // Create mock request
        $request = new Request(['router_id' => $router->id]);
        
        // Test controller method
        $controller = new PackageController();
        $response = $controller->getPppProfiles($request);
        
        $this->info("Response status: {$response->getStatusCode()}");
        $this->info("Response content: {$response->getContent()}");
        
        // Test with another router
        $router2 = Router::skip(1)->first();
        if ($router2) {
            $this->info("\nTesting with Router 2: {$router2->name}");
            $request2 = new Request(['router_id' => $router2->id]);
            $response2 = $controller->getPppProfiles($request2);
            $this->info("Response 2 status: {$response2->getStatusCode()}");
            $this->info("Response 2 content: {$response2->getContent()}");
        }
    }
}
