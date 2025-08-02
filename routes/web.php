<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PppoeController;
use App\Http\Controllers\PppProfileController;
use App\Http\Controllers\ProfileController;

// Debug route tanpa middleware authentication (di luar semua middleware)
Route::get('/test-monitor/{router}', function(App\Models\Router $router) {
    try {
        $controller = new App\Http\Controllers\RouterController(new App\Services\MikrotikService());
        $response = $controller->getSystemInfo($router);
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
    Route::get('/routers/{router}/status', [RouterController::class, 'getRouterStatus'])
        ->name('routers.status');
    Route::get('/routers/{router}/monitor', [RouterController::class, 'monitor'])
        ->name('routers.monitor');
    Route::get('/routers/{router}/monitor/interfaces', [RouterController::class, 'getInterfaces'])
        ->name('routers.monitor.interfaces');
    Route::get('/routers/{router}/monitor/ppp', [RouterController::class, 'getPppSessions'])
        ->name('routers.monitor.ppp');
    Route::get('/routers/{router}/monitor/ip-addresses', [RouterController::class, 'getIpAddresses'])
        ->name('routers.monitor.ip-addresses');
    Route::get('/routers/{router}/monitor/dhcp-leases', [RouterController::class, 'getDhcpLeases'])
        ->name('routers.monitor.dhcp-leases');
    Route::get('/routers/{router}/monitor/firewall', [RouterController::class, 'getFirewallRules'])
        ->name('routers.monitor.firewall');
    Route::get('/routers/{router}/monitor/system-info', [RouterController::class, 'getSystemInfo'])
        ->name('routers.monitor.system-info');
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
    
    // Profile Settings (available for all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Profile Settings routes are included in the authenticated group above