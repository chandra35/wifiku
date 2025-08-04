<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PppoeController;
use App\Http\Controllers\PppProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CoveredAreaController;

// Debug route tanpa middleware authentication (di luar semua middleware)
Route::get('/test-monitor/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getBasicSystemInfo($router);
        return $response;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Test BGP lookup with detected IP
Route::get('/test-bgp-lookup', function() {
    try {
        $bgpService = new App\Services\BgpToolsService();
        $ip = '103.133.61.228'; // Use the detected IP
        
        $bgpResult = $bgpService->getIspInfo($ip);
        $whoisResult = $bgpService->getWhoisInfo($ip);
        
        return response()->json([
            'ip_tested' => $ip,
            'bgp_result' => $bgpResult,
            'whois_result' => $whoisResult,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(), 
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test ISP info with router detection (simplified)
Route::get('/test-isp-simple', function() {
    try {
        $router = App\Models\Router::first();
        if (!$router) {
            return response()->json(['error' => 'No router found']);
        }
        
        $bgpService = new App\Services\BgpToolsService();
        
        // Get public IP (simulated)
        $publicIp = '103.133.61.228'; // Use the known working IP
        
        // Test BGP lookup
        $bgpResult = $bgpService->getIspInfo($publicIp);
        
        return response()->json([
            'router_info' => [
                'id' => $router->id,
                'name' => $router->name,
                'ip' => $router->ip_address
            ],
            'public_ip' => $publicIp,
            'bgp_result' => $bgpResult,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(), 
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Direct test for ISP info endpoint (no auth for testing)
Route::get('/test-direct-isp/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $controller->setBgpToolsService(new App\Services\BgpToolsService());
        
        $response = $controller->getIspInfo($router);
        return $response;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Test public IP detection method
Route::get('/test-ip-methods', function() {
    try {
        $router = App\Models\Router::first();
        if (!$router) {
            return response()->json(['error' => 'No router found in database']);
        }
        
        $controller = new App\Http\Controllers\RouterController(
            new App\Services\MikrotikService(), 
            new App\Services\BgpToolsService()
        );
        
        // Test server public IP first
        $reflection = new ReflectionClass($controller);
        $serverMethod = $reflection->getMethod('getServerPublicIp');
        $serverMethod->setAccessible(true);
        $serverIp = $serverMethod->invoke($controller);
        
        // Test router public IP  
        $routerMethod = $reflection->getMethod('getRouterPublicIp');
        $routerMethod->setAccessible(true);
        $routerIp = $routerMethod->invoke($controller, $router);
        
        return response()->json([
            'router_info' => [
                'id' => $router->id,
                'name' => $router->name,
                'host' => $router->host
            ],
            'server_public_ip' => $serverIp,
            'router_public_ip' => $routerIp,
            'methods_tested' => [
                'server_fallback' => $serverIp ? 'success' : 'failed',
                'router_mikrotik' => $routerIp ? 'success' : 'failed'
            ],
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(), 
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test public IP detection
Route::get('/test-public-ip/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(
            new App\Services\MikrotikService(), 
            new App\Services\BgpToolsService()
        );
        
        // Use reflection to access private method for testing
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getRouterPublicIp');
        $method->setAccessible(true);
        
        $publicIp = $method->invoke($controller, $router);
        
        return response()->json([
            'router_id' => $router->id,
            'router_name' => $router->name,
            'public_ip' => $publicIp,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

Route::get('/test-system/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getBasicSystemInfo($router);
        return $response;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

Route::get('/debug-system-identity/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getSystemIdentity($router);
        return $response;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

// Debug route tanpa middleware authentication
Route::get('/debug-system-info/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getSystemInfo($router);
        return $response;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Debug route untuk getRouterStatusInfo tanpa auth
Route::get('/debug-router-status/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getRouterStatusInfo($router);
        return response()->json([
            'method' => 'getRouterStatusInfo',
            'data' => $response,
            'router' => $router->name
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Debug endpoint untuk test router status tanpa auth
Route::get('/test-status/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        return $controller->getRouterStatus($router);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Simple test endpoint
Route::get('/test-ajax', function() {
    return response()->json(['success' => true, 'message' => 'AJAX working', 'timestamp' => now()]);
});

// Test monitor page
Route::get('/test-monitor/{router}', function(App\Models\Router $router) {
    return view('test-monitor', compact('router'));
});

// Redirect /home to dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Routes for Super Admin only
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    // Router Management
    Route::resource('routers', RouterController::class);
    Route::post('/routers/test-connection', [RouterController::class, 'testConnection'])
        ->name('routers.test-connection');
    
    // Router Ping Monitoring
    Route::get('/routers/ping-data', [RouterController::class, 'getPingData'])
        ->name('routers.ping-data');
    
    // Debug ping
    Route::get('/debug/ping-cache', function() {
        $routers = App\Models\Router::where('status', 'active')->get();
        $data = [];
        foreach($routers as $router) {
            $cached = Cache::get("router_ping_{$router->id}");
            $data[$router->id] = [
                'router' => $router->name,
                'cache_key' => "router_ping_{$router->id}",
                'cached_data' => $cached
            ];
        }
        return response()->json($data);
    });
    
    // Network Tools Routes
    Route::get('/routers/{router}/network-tools', [RouterController::class, 'monitor'])
        ->name('routers.monitor');
    Route::get('/routers/{router}/status', [RouterController::class, 'getRouterStatus'])
        ->name('routers.status');
    Route::post('/routers/{router}/ping', [RouterController::class, 'ping'])
        ->name('routers.ping');
    Route::post('/routers/{router}/traceroute', [RouterController::class, 'traceroute'])
        ->name('routers.traceroute');
    Route::post('/routers/{router}/dns-resolve', [RouterController::class, 'dnsResolve'])
        ->name('routers.dns-resolve');
    Route::get('/routers/{router}/interfaces', [RouterController::class, 'getInterfaces'])
        ->name('routers.interfaces');
    Route::get('/routers/{router}/monitor/system-info', [RouterController::class, 'getSystemInfoApi'])
        ->name('routers.monitor.system-info');
    Route::get('/routers/{router}/monitor/system-identity', [RouterController::class, 'getSystemIdentity'])
        ->name('routers.monitor.system-identity');
    Route::get('/routers/{router}/monitor/network-traffic', [RouterController::class, 'getNetworkTraffic'])
        ->name('routers.monitor.network-traffic');
    Route::get('/routers/{router}/monitor/gateway-traffic', [RouterController::class, 'getGatewayTraffic'])
        ->name('routers.monitor.gateway-traffic');
    Route::get('/routers/{router}/monitor/isp-info', [RouterController::class, 'getIspInfo'])
        ->name('routers.monitor.isp-info');
    Route::get('/routers/{router}/monitor/logs', [RouterController::class, 'getSystemLogs'])
        ->name('routers.monitor.logs');
    
    // Debug route - temporary
    Route::get('/debug/router-monitor/{router}', function(App\Models\Router $router) {
        $user = auth()->user();
        return response()->json([
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ] : null,
            'user_role' => $user && $user->role ? $user->role->name : null,
            'is_super_admin' => $user && $user->role ? $user->role->name === 'super_admin' : false,
            'router' => [
                'id' => $router->id,
                'name' => $router->name,
                'ip_address' => $router->ip_address
            ],
            'authenticated' => auth()->check()
        ]);
    });
    
    // Test route untuk debug AJAX
    Route::get('/test-ajax/{router}', function(App\Models\Router $router) {
        return response()->json([
            'success' => true,
            'message' => 'AJAX test successful',
            'router_name' => $router->name,
            'timestamp' => now()
        ]);
    });
    
    // Debug system info tanpa middleware
    Route::get('/debug-system-info/{router}', function(App\Models\Router $router) {
        try {
            $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
            $response = $controller->getSystemInfo($router);
            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    });
    
    // Debug route for router status
    Route::get('/debug/router-status/{router}', function(App\Models\Router $router) {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $result = $controller->getRouterStatus($router);
        return $result;
    })->name('debug.router.status');
    
    // User Management
    Route::resource('users', UserController::class);
});

// Routes for both Super Admin and Admin
Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    // PPPoE Management
    Route::resource('pppoe', PppoeController::class);
    Route::post('/pppoe/get-profiles', [PppoeController::class, 'getProfiles'])
        ->name('pppoe.get-profiles');
    Route::post('/pppoe/{pppoe}/sync-to-mikrotik', [PppoeController::class, 'syncToMikrotik'])
        ->name('pppoe.sync-to-mikrotik');
    Route::post('/pppoe/{pppoe}/show-password', [PppoeController::class, 'showPassword'])
        ->name('pppoe.show-password');
    Route::post('/pppoe/{pppoe}/get-stats', [PppoeController::class, 'getStats'])
        ->name('pppoe.get-stats');
    Route::post('/pppoe/{pppoe}/check-sync-status', [PppoeController::class, 'checkSyncStatus'])
        ->name('pppoe.check-sync-status');
    Route::post('/pppoe/import-from-mikrotik', [PppoeController::class, 'importFromMikrotik'])
        ->name('pppoe.import-from-mikrotik');
    Route::post('/pppoe/preview-import', [PppoeController::class, 'previewImport'])
        ->name('pppoe.preview-import');
    Route::post('/pppoe/import-selected', [PppoeController::class, 'importSelected'])
        ->name('pppoe.import-selected');
    
    // PPP Profile Management
    Route::resource('ppp-profiles', PppProfileController::class);
    Route::post('/ppp-profiles/{pppProfile}/sync-to-mikrotik', [PppProfileController::class, 'syncToMikrotik'])
        ->name('ppp-profiles.sync-to-mikrotik');
    Route::post('/ppp-profiles/import-from-mikrotik', [PppProfileController::class, 'importFromMikrotik'])
        ->name('ppp-profiles.import-from-mikrotik');
    Route::post('/ppp-profiles/preview-import', [PppProfileController::class, 'previewImport'])
        ->name('ppp-profiles.preview-import');
    Route::post('/ppp-profiles/import-selected', [PppProfileController::class, 'importSelected'])
        ->name('ppp-profiles.import-selected');
    
    // Covered Areas Management - untuk Admin/POP mengelola area coverage
    Route::resource('covered-areas', CoveredAreaController::class);
    Route::post('/covered-areas/{coveredArea}/toggle-status', [CoveredAreaController::class, 'toggleStatus'])
        ->name('covered-areas.toggle-status');
    Route::get('/api/covered-areas/provinces', [CoveredAreaController::class, 'getProvinces'])
        ->name('api.areas.provinces');
    Route::get('/api/covered-areas/provinces/{provinceId}/cities', [CoveredAreaController::class, 'getCities'])
        ->name('api.covered-areas.cities');
    Route::get('/api/covered-areas/cities/{cityId}/districts', [CoveredAreaController::class, 'getDistricts'])
        ->name('api.covered-areas.districts');
    Route::get('/api/covered-areas/districts/{districtId}/villages', [CoveredAreaController::class, 'getVillages'])
        ->name('api.covered-areas.villages');
    
    // Profile Settings (available for all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/pic-photo', [ProfileController::class, 'deletePicPhoto'])->name('profile.delete-pic-photo');
    Route::delete('/profile/isp-logo', [ProfileController::class, 'deleteIspLogo'])->name('profile.delete-isp-logo');
});

// Test company info for invoice development
// Profile Settings routes are included in the authenticated group above