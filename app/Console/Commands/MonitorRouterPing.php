<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\MikrotikService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitorRouterPing extends Command
{
    use HandlesMikrotikConnection;

    protected $signature = 'router:monitor-ping {--interval=5 : Ping interval in seconds}';
    protected $description = 'Monitor ping to 8.8.8.8 for all active routers continuously';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $interval = (int) $this->option('interval');
        $this->info("Starting router ping monitoring (interval: {$interval}s)");
        $this->info("Press Ctrl+C to stop...");

        while (true) {
            $activeRouters = Router::where('status', 'active')->get();
            
            if ($activeRouters->count() === 0) {
                $this->warn('No active routers found. Waiting...');
                sleep($interval);
                continue;
            }

            $this->line("Monitoring " . $activeRouters->count() . " active routers...");

            foreach ($activeRouters as $router) {
                $this->monitorSingleRouter($router);
            }

            sleep($interval);
        }
    }

    private function monitorSingleRouter(Router $router)
    {
        try {
            $this->line("Pinging {$router->name} ({$router->ip_address})...");

            // Connect to router
            $connected = $this->connectToMikrotik($router);
            
            if (!$connected) {
                $this->cachePingResult($router->id, null, 'Connection failed');
                return;
            }

            // Execute ping
            $pingResult = $this->mikrotikService->ping('8.8.8.8', 1); // Single ping for speed
            
            if ($pingResult['success']) {
                $avgTime = $pingResult['data']['avg_time'];
                $this->cachePingResult($router->id, $avgTime);
                $this->info("  ✓ {$router->name}: {$avgTime}ms");
            } else {
                $this->cachePingResult($router->id, null, $pingResult['message']);
                $this->warn("  ✗ {$router->name}: {$pingResult['message']}");
            }

        } catch (\Exception $e) {
            $this->error("  ✗ {$router->name}: " . $e->getMessage());
            $this->cachePingResult($router->id, null, $e->getMessage());
        }
    }

    private function cachePingResult($routerId, $pingTime, $error = null)
    {
        $data = [
            'ping_time' => $pingTime,
            'error' => $error,
            'timestamp' => now()->toISOString(),
            'status' => $pingTime !== null ? 'success' : 'failed'
        ];

        // Cache for 30 seconds (longer than ping interval to avoid gaps)
        Cache::put("router_ping_{$routerId}", $data, 30);
    }
}
