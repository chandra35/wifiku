<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use Illuminate\Support\Facades\Crypt;

class FixRouterPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:fix-passwords {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix router passwords by converting from hash to encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = $this->argument('password');
        
        if (!$password) {
            $password = $this->secret('Enter the actual password for the router:');
        }

        $routers = Router::all();
        
        if ($routers->isEmpty()) {
            $this->info('No routers found.');
            return;
        }

        foreach ($routers as $router) {
            $this->info("Processing router: {$router->name} ({$router->ip_address})");
            
            try {
                // Try to decrypt first to see if it's already encrypted
                Crypt::decryptString($router->password);
                $this->info("Password for {$router->name} is already encrypted.");
            } catch (\Exception $e) {
                // Password is hashed, need to update with encrypted version
                $router->update([
                    'password' => Crypt::encryptString($password)
                ]);
                $this->info("Updated password for {$router->name} to use encryption.");
            }
        }
        
        $this->info('Router password fix completed!');
    }
}
