<?php

namespace App\Traits;

use App\Models\Router;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

trait HandlesMikrotikConnection
{
    /**
     * Get decrypted password for router
     */
    protected function getDecryptedRouterPassword(Router $router): ?string
    {
        try {
            $decrypted = Crypt::decryptString($router->password);
            Log::info('Router password decrypted successfully', [
                'router_id' => $router->id,
                'router_name' => $router->name
            ]);
            return $decrypted;
        } catch (\Exception $e) {
            Log::error('Failed to decrypt router password', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Connect to MikroTik router with proper password decryption
     */
    protected function connectToMikrotik(Router $router): bool
    {
        $password = $this->getDecryptedRouterPassword($router);
        
        if ($password === null) {
            Log::error('Cannot connect to router: password decryption failed', [
                'router_id' => $router->id,
                'router_name' => $router->name
            ]);
            return false;
        }

        Log::info('Attempting MikroTik connection', [
            'router_id' => $router->id,
            'ip_address' => $router->ip_address,
            'username' => $router->username,
            'port' => $router->port
        ]);

        $connected = $this->mikrotikService->connect(
            $router->ip_address,
            $router->username,
            $password,
            $router->port
        );

        Log::info('MikroTik connection result', [
            'router_id' => $router->id,
            'connected' => $connected
        ]);

        return $connected;
    }
}
