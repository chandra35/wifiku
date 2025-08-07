<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Services\BgpToolsService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use RouterOS\Query;

class RouterController extends Controller
{
    use HandlesMikrotikConnection;
    protected $mikrotikService;
    protected $bgpToolsService;

    public function __construct(MikrotikService $mikrotikService, BgpToolsService $bgpToolsService)
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin')->except([
            'getPingData', 
            'show', 
            'getSystemIdentity', 
            'getSystemInfoApi', 
            'getNetworkTraffic', 
            'getGatewayTraffic', 
            'getIspInfo'
        ]);
        $this->mikrotikService = $mikrotikService;
        $this->bgpToolsService = $bgpToolsService;
    }

    /**
     * Set BGP Tools Service (for testing purposes)
     */
    public function setBgpToolsService(BgpToolsService $bgpToolsService)
    {
        $this->bgpToolsService = $bgpToolsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routers = Router::latest()->paginate(10);
        return view('routers.index', compact('routers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('routers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'description' => 'nullable|string',
        ]);

        // Test connection before saving
        $testResult = $this->mikrotikService->testConnection(
            $request->ip_address,
            $request->username,
            $request->password,
            $request->port ?? 8728
        );

        if (!$testResult['success']) {
            return back()->withErrors(['connection' => $testResult['message']])->withInput();
        }

        Router::create([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'password' => Crypt::encryptString($request->password),
            'port' => $request->port ?? 8728,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->route('routers.index')
            ->with('success', 'Router berhasil ditambahkan dan koneksi telah diverifikasi.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Router $router)
    {
        return view('routers.show', compact('router'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Router $router)
    {
        return view('routers.edit', compact('router'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Router $router)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers,ip_address,' . $router->id,
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $updateData = [
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'port' => $request->port ?? 8728,
            'description' => $request->description,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            // Test connection with new password if provided
            $testResult = $this->mikrotikService->testConnection(
                $request->ip_address,
                $request->username,
                $request->password,
                $request->port ?? 8728
            );

            if (!$testResult['success']) {
                return back()->withErrors(['connection' => $testResult['message']])->withInput();
            }

            $updateData['password'] = Crypt::encryptString($request->password);
        }

        $router->update($updateData);

        return redirect()->route('routers.index')
            ->with('success', 'Router berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Router $router)
    {
        $router->delete();
        
        return redirect()->route('routers.index')
            ->with('success', 'Router berhasil dihapus.');
    }

    /**
     * Test connection to router
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
        ]);

        Log::info('Router test connection requested', [
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'password_length' => strlen($request->password ?? ''),
            'password_is_null' => is_null($request->password),
            'password_is_empty' => empty($request->password),
            'port' => $request->port ?? 8728,
            'user_id' => auth()->id()
        ]);

        $result = $this->mikrotikService->testConnection(
            $request->ip_address,
            $request->username,
            $request->password,
            $request->port ?? 8728
        );

        Log::info('Router test connection result', ['result' => $result]);

        return response()->json($result);
    }

    /**
     * Show network tools for the router
     */
    public function networkTools(Router $router)
    {
        Log::info('Network Tools accessed', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role
        ]);

        return view('routers.network-tools', compact('router'));
    }

    /**
     * Execute ping command via MikroTik
     */
    public function ping(Request $request, Router $router)
    {
        $request->validate([
            'target' => 'required|string|max:255',
            'count' => 'integer|min:1|max:10'
        ]);

        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
            }

            $client = $this->mikrotikService->getClient();
            $target = $request->input('target');
            $count = $request->input('count', 4);

            // Execute ping command using MikroTik API
            $query = new \RouterOS\Query('/ping');
            $query->equal('address', $target);
            $query->equal('count', $count);
            
            $response = $client->query($query)->read();

            return response()->json([
                'success' => true,
                'data' => $response,
                'command' => "ping {$target} count={$count}"
            ]);

        } catch (\Exception $e) {
            Log::error('Ping command failed', [
                'router_id' => $router->id,
                'target' => $request->input('target'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ping failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute traceroute command via MikroTik
     */
    public function traceroute(Request $request, Router $router)
    {
        $request->validate([
            'target' => 'required|string|max:255'
        ]);

        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
            }

            $client = $this->mikrotikService->getClient();
            $target = $request->input('target');

            // Execute traceroute command using MikroTik API
            $query = new \RouterOS\Query('/tool/traceroute');
            $query->equal('address', $target);
            
            $response = $client->query($query)->read();

            return response()->json([
                'success' => true,
                'data' => $response,
                'command' => "traceroute {$target}"
            ]);

        } catch (\Exception $e) {
            Log::error('Traceroute command failed', [
                'router_id' => $router->id,
                'target' => $request->input('target'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Traceroute failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute DNS resolve via MikroTik
     */
    public function dnsResolve(Request $request, Router $router)
    {
        $request->validate([
            'hostname' => 'required|string|max:255'
        ]);

        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
            }

            $client = $this->mikrotikService->getClient();
            $hostname = $request->input('hostname');

            // Execute DNS resolve using MikroTik API
            $query = new \RouterOS\Query('/tool/dns-lookup');
            $query->equal('name', $hostname);
            
            $response = $client->query($query)->read();

            return response()->json([
                'success' => true,
                'data' => $response,
                'command' => "dns-lookup {$hostname}"
            ]);

        } catch (\Exception $e) {
            Log::error('DNS resolve failed', [
                'router_id' => $router->id,
                'hostname' => $request->input('hostname'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'DNS resolve failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get router interfaces for network tools
     */
    public function getInterfaces(Router $router)
    {
        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
            }

            $client = $this->mikrotikService->getClient();

            // Get interface list
            $query = new \RouterOS\Query('/interface/print');
            $response = $client->query($query)->read();

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Get interfaces failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get interfaces: ' . $e->getMessage()
            ]);
        }
    }

    public function getRouterStatus(Router $router)
    {
        try {
            $statusInfo = $this->getRouterStatusInfo($router);
            
            // Return the original format for compatibility with Router Management
            return response()->json($statusInfo);
            
        } catch (\Exception $e) {
            Log::error('Router status endpoint error', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'connected' => false,
                'cpu_load' => 'N/A',
                'memory_usage' => 'N/A',
                'memory_usage_percent' => 0,
                'active_ppp_sessions' => 'N/A',
                'ping_8888' => 'N/A',
                'uptime' => 'N/A',
                'error' => 'Status check failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get router status information
     */
    private function getRouterStatusInfo(Router $router): array
    {
        try {
            Log::info('Getting router status info', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'router_ip' => $router->ip_address
            ]);

            // Use trait method for connection
            $connected = $this->connectToMikrotik($router);

            if (!$connected) {
                Log::warning('Failed to connect to router', [
                    'router_id' => $router->id,
                    'ip' => $router->ip_address
                ]);
                return [
                    'connected' => false,
                    'cpu_load' => 'N/A',
                    'memory_usage' => 'N/A',
                    'memory_usage_percent' => 0,
                    'active_ppp_sessions' => 'N/A',
                    'ping_8888' => 'N/A',
                    'uptime' => 'N/A',
                    'error' => 'Connection failed'
                ];
            }

            Log::info('Successfully connected, getting router status');

            // Get router status
            $statusResult = $this->mikrotikService->getRouterStatus();

            Log::info('Router status result', [
                'success' => $statusResult['success'],
                'router_id' => $router->id
            ]);

            if (!$statusResult['success']) {
                return [
                    'connected' => false,
                    'cpu_load' => 'N/A',
                    'memory_usage' => 'N/A',
                    'memory_usage_percent' => 0,
                    'active_ppp_sessions' => 'N/A',
                    'ping_8888' => 'N/A',
                    'uptime' => 'N/A',
                    'error' => $statusResult['message']
                ];
            }

            $data = $statusResult['data'];
            
            Log::info('Router status data received', [
                'data' => $data,
                'cpu_load' => $data['cpu_load'] ?? 'NOT_SET',
                'cpu_load_type' => gettype($data['cpu_load'] ?? null)
            ]);
            
            $cpuLoad = $data['cpu_load'] ?? 0;
            $formattedCpuLoad = $cpuLoad . '%';
            
            Log::info('CPU Load formatting', [
                'original' => $cpuLoad,
                'formatted' => $formattedCpuLoad
            ]);
            
            return [
                'connected' => true,
                'cpu_load' => $formattedCpuLoad,
                'memory_usage' => $this->mikrotikService->formatMemorySize($data['used_memory']) . ' / ' . 
                                $this->mikrotikService->formatMemorySize($data['total_memory']),
                'memory_usage_percent' => $data['memory_usage_percent'],
                'active_ppp_sessions' => $data['active_ppp_sessions'],
                'ping_8888' => $this->getPingToGoogle($router),
                'uptime' => $data['uptime'],
                'version' => $data['version'] ?? 'Unknown',
                'board_name' => $data['board_name'] ?? 'Unknown'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get router status for router ' . $router->id, [
                'error' => $e->getMessage(),
                'router_ip' => $router->ip_address
            ]);

            return [
                'connected' => false,
                'cpu_load' => 'N/A',
                'memory_usage' => 'N/A',
                'memory_usage_percent' => 0,
                'active_ppp_sessions' => 'N/A',
                'ping_8888' => 'N/A',
                'uptime' => 'N/A',
                'error' => 'Connection error'
            ];
        }
    }

    /**
     * Get ping response time to 8.8.8.8
     */
    private function getPingToGoogle(Router $router): string
    {
        try {
            // First try to get from cache (background monitoring)
            $cachedPing = Cache::get("router_ping_{$router->id}");
            
            if ($cachedPing && $cachedPing['status'] === 'success' && $cachedPing['ping_time'] !== null) {
                return $cachedPing['ping_time'] . 'ms';
            }

            // Fallback to direct ping if cache is empty or failed
            // Check if router is already connected, if not connect
            if (!$this->mikrotikService || !$this->mikrotikService->isConnected()) {
                $connected = $this->connectToMikrotik($router);
                if (!$connected) {
                    return 'N/A';
                }
            }

            // Execute ping command to 8.8.8.8
            $pingResult = $this->mikrotikService->ping('8.8.8.8', 1); // Single ping for speed
            
            if ($pingResult['success'] && isset($pingResult['data']['avg_time'])) {
                return $pingResult['data']['avg_time'] . 'ms';
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            Log::error('Failed to ping 8.8.8.8 from router ' . $router->id, [
                'error' => $e->getMessage()
            ]);
            return 'N/A';
        }
    }

    /**
     * Get real-time ping data for all active routers
     */
    public function getPingData()
    {
        try {
            $activeRouters = Router::where('status', 'active')->get(['id', 'name']);
            $pingData = [];

            foreach ($activeRouters as $router) {
                $cachedPing = Cache::get("router_ping_{$router->id}");
                
                if ($cachedPing) {
                    $pingData[$router->id] = [
                        'router_name' => $router->name,
                        'ping_time' => $cachedPing['ping_time'],
                        'status' => $cachedPing['status'],
                        'error' => $cachedPing['error'] ?? null,
                        'timestamp' => $cachedPing['timestamp'],
                        'display' => $cachedPing['ping_time'] ? $cachedPing['ping_time'] . 'ms' : 'N/A'
                    ];
                } else {
                    $pingData[$router->id] = [
                        'router_name' => $router->name,
                        'ping_time' => null,
                        'status' => 'no_data',
                        'error' => 'No ping data available',
                        'timestamp' => now()->toISOString(),
                        'display' => 'N/A'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $pingData,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get ping data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get ping data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display router monitoring dashboard
     */
    public function monitor(Router $router)
    {
        // Debug: Log the access attempt
        Log::info('Monitor dashboard accessed', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role->name ?? 'no_role'
        ]);
        
        // Get initial system info for server-side rendering
        $systemInfo = null;
        try {
            // Use testConnection instead of direct query to avoid "incorrect endpoint" error
            $password = $this->getDecryptedRouterPassword($router);
            if ($password && $this->mikrotikService->testConnection($router->ip_address, $router->username, $password, $router->port)['success']) {
                $systemInfo = [
                    'connected' => true,
                    'identity' => [['name' => $router->name]]
                ];
            } else {
                $systemInfo = ['connected' => false, 'error' => 'Connection failed'];
            }
        } catch (\Exception $e) {
            Log::error('Error getting initial system info: ' . $e->getMessage());
            $systemInfo = ['connected' => false, 'error' => $e->getMessage()];
        }
        
        return view('routers.monitor', compact('router', 'systemInfo'));
    }

    /**
     * Get PPP sessions information
     */
    public function getPppSessions(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get active PPP sessions using Query class
            $sessionsQuery = new \RouterOS\Query('/ppp/active/print');
            $sessions = $client->query($sessionsQuery)->read();

            // Get PPP secrets using Query class
            $secretsQuery = new \RouterOS\Query('/ppp/secret/print');
            $secrets = $client->query($secretsQuery)->read();

            return response()->json([
                'success' => true,
                'active_sessions' => $sessions,
                'secrets' => $secrets
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting PPP sessions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get IP addresses information
     */
    public function getIpAddresses(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get IP addresses using Query class
            $addressQuery = new \RouterOS\Query('/ip/address/print');
            $addresses = $client->query($addressQuery)->read();

            // Get routes using Query class
            $routesQuery = new \RouterOS\Query('/ip/route/print');
            $routes = $client->query($routesQuery)->read();

            return response()->json([
                'success' => true,
                'addresses' => $addresses,
                'routes' => $routes
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting IP addresses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get DHCP leases information
     */
    public function getDhcpLeases(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get DHCP leases using Query class
            $leasesQuery = new \RouterOS\Query('/ip/dhcp-server/lease/print');
            $leases = $client->query($leasesQuery)->read();

            return response()->json([
                'success' => true,
                'leases' => $leases
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting DHCP leases: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get firewall rules information
     */
    public function getFirewallRules(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get firewall filter rules using Query class
            $filterQuery = new \RouterOS\Query('/ip/firewall/filter/print');
            $filterRules = $client->query($filterQuery)->read();

            // Get firewall NAT rules using Query class
            $natQuery = new \RouterOS\Query('/ip/firewall/nat/print');
            $natRules = $client->query($natQuery)->read();

            return response()->json([
                'success' => true,
                'filter_rules' => $filterRules,
                'nat_rules' => $natRules
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting firewall rules: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show simple system monitor page
     */
    public function systemMonitor(Router $router)
    {
        return view('routers.system-monitor', compact('router'));
    }
    
    /**
     * Get basic system information for simple monitor
     */
    public function getBasicSystemInfo(Router $router)
    {
        Log::info('Getting basic system info for router: ' . $router->name);
        
        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot connect to router'
                ]);
            }

            $client = $this->mikrotikService->getClient();
            $systemInfo = [];
            
            // Get System Identity
            try {
                $identityQuery = new \RouterOS\Query('/system/identity/print');
                $identity = $client->query($identityQuery)->read();
                $systemInfo['identity'] = $identity[0]['name'] ?? $router->name;
            } catch (\Exception $e) {
                $systemInfo['identity'] = $router->name;
            }
            
            // Get System Resource
            try {
                $resourceQuery = new \RouterOS\Query('/system/resource/print');
                $resource = $client->query($resourceQuery)->read();
                if (!empty($resource)) {
                    $res = $resource[0];
                    $systemInfo['version'] = $res['version'] ?? 'Unknown';
                    $systemInfo['board'] = $res['board-name'] ?? 'Unknown';
                    $systemInfo['cpu'] = $res['cpu'] ?? 'Unknown';
                    $systemInfo['cpu_load'] = $res['cpu-load'] ?? '0';
                    $systemInfo['uptime'] = $res['uptime'] ?? '0s';
                    $systemInfo['total_memory'] = isset($res['total-memory']) ? $this->formatBytes($res['total-memory']) : 'Unknown';
                    $systemInfo['free_memory'] = isset($res['free-memory']) ? $this->formatBytes($res['free-memory']) : 'Unknown';
                    $systemInfo['memory_usage'] = $this->calculateMemoryUsage($res['total-memory'] ?? 0, $res['free-memory'] ?? 0);
                }
            } catch (\Exception $e) {
                Log::error('Error getting resource: ' . $e->getMessage());
                $systemInfo['version'] = 'Unknown';
                $systemInfo['board'] = 'Unknown';
                $systemInfo['cpu'] = 'Unknown';
                $systemInfo['cpu_load'] = '0';
                $systemInfo['uptime'] = '0s';
                $systemInfo['total_memory'] = 'Unknown';
                $systemInfo['free_memory'] = 'Unknown';
                $systemInfo['memory_usage'] = '0';
            }
            
            // Get System Clock
            try {
                $clockQuery = new \RouterOS\Query('/system/clock/print');
                $clock = $client->query($clockQuery)->read();
                $systemInfo['date'] = $clock[0]['date'] ?? 'Unknown';
                $systemInfo['time'] = $clock[0]['time'] ?? 'Unknown';
            } catch (\Exception $e) {
                $systemInfo['date'] = 'Unknown';
                $systemInfo['time'] = 'Unknown';
            }

            return response()->json([
                'success' => true,
                'data' => $systemInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting basic system info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Helper function to format bytes
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $sizes[$factor];
    }
    
    /**
     * Helper function to calculate memory usage percentage
     */
    private function calculateMemoryUsage($total, $free)
    {
        if ($total == 0) return '0';
        
        $used = $total - $free;
        $percentage = ($used / $total) * 100;
        
        return number_format($percentage, 1);
    }

    /**
     * Get original system information - keeping for backward compatibility
     */
    public function getSystemInfo(Router $router)
    {
        Log::info('Starting getSystemInfo for router: ' . $router->name . ' (' . $router->ip_address . ')');
        
        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
            }

            $client = $this->mikrotikService->getClient();
            
            // Get system information using the same method as Router Management
            $systemInfo = [];
            
            try {
                // Get system identity using Query class like in getSystemResource
                $identityQuery = new \RouterOS\Query('/system/identity/print');
                $identity = $client->query($identityQuery)->read();
                $systemInfo['identity'] = $identity;
                Log::info('Identity retrieved: ' . json_encode($identity));
            } catch (\Exception $e) {
                Log::error('Error getting identity: ' . $e->getMessage());
                $systemInfo['identity'] = [['name' => $router->name]];
            }
            
            try {
                // Get system resource using Query class
                $resourceQuery = new \RouterOS\Query('/system/resource/print');
                $resource = $client->query($resourceQuery)->read();
                $systemInfo['resource'] = $resource;
                Log::info('Resource retrieved: ' . json_encode($resource));
            } catch (\Exception $e) {
                Log::error('Error getting resource: ' . $e->getMessage());
                $systemInfo['resource'] = [['version' => 'Unknown', 'cpu-load' => '0%']];
            }
            
            try {
                // Get system routerboard using Query class
                $routerboardQuery = new \RouterOS\Query('/system/routerboard/print');
                $routerboard = $client->query($routerboardQuery)->read();
                $systemInfo['routerboard'] = $routerboard;
                Log::info('Routerboard retrieved: ' . json_encode($routerboard));
            } catch (\Exception $e) {
                Log::error('Error getting routerboard: ' . $e->getMessage());
                $systemInfo['routerboard'] = [['model' => 'Unknown']];
            }

            return response()->json([
                'success' => true,
                'identity' => $systemInfo['identity'],
                'resource' => $systemInfo['resource'],
                'routerboard' => $systemInfo['routerboard'],
                'message' => 'System information retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting system info: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get system logs
     */
    public function getSystemLogs(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get system logs using Query class (simplified version)
            $logsQuery = new \RouterOS\Query('/log/print');
            $logs = $client->query($logsQuery)->read();

            return response()->json([
                'success' => true,
                'logs' => array_slice($logs, -50) // Get last 50 logs
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting system logs: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get system information for router detail page
     */
    public function getSystemInfoApi(Router $router)
    {
        Log::info('Getting system info API for router: ' . $router->name);
        
        try {
            $connected = $this->connectToMikrotik($router);
            if (!$connected) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot connect to router'
                ]);
            }

            $client = $this->mikrotikService->getClient();
            $systemInfo = [];
            
            // Get System Resource
            try {
                $resourceQuery = new \RouterOS\Query('/system/resource/print');
                $resource = $client->query($resourceQuery)->read();
                if (!empty($resource)) {
                    $res = $resource[0];
                    $systemInfo['version'] = $res['version'] ?? 'Unknown';
                    $systemInfo['board_name'] = $res['board-name'] ?? 'Unknown';
                    $systemInfo['architecture'] = $res['architecture-name'] ?? 'Unknown';
                    $systemInfo['cpu'] = $res['cpu'] ?? 'Unknown';
                    $systemInfo['cpu_load'] = $res['cpu-load'] ?? '0%';
                    $systemInfo['uptime'] = $res['uptime'] ?? '0s';
                    
                    // Format memory information
                    if (isset($res['total-memory'])) {
                        $systemInfo['total_memory'] = $res['total-memory'];
                        $systemInfo['total_memory_formatted'] = $this->formatBytes($res['total-memory']);
                    }
                    
                    if (isset($res['free-memory'])) {
                        $systemInfo['free_memory'] = $res['free-memory'];
                        $systemInfo['free_memory_formatted'] = $this->formatBytes($res['free-memory']);
                    }
                    
                    if (isset($res['total-memory']) && isset($res['free-memory'])) {
                        $used = $res['total-memory'] - $res['free-memory'];
                        $systemInfo['used_memory_formatted'] = $this->formatBytes($used);
                        $systemInfo['memory_usage_percent'] = $this->calculateMemoryUsage($res['total-memory'], $res['free-memory']) . '%';
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error getting resource: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get system resource information'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $systemInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting system info API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get router system identity
     */
    public function getSystemIdentity(Router $router)
    {
        try {
            Log::info('Getting system identity', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'router_ip' => $router->ip_address
            ]);

            // Use trait method for connection
            $connected = $this->connectToMikrotik($router);
            
            if (!$connected) {
                Log::warning('Failed to connect to router for system identity', [
                    'router_id' => $router->id,
                    'ip' => $router->ip_address
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to router'
                ]);
            }

            Log::info('Connected successfully, getting system identity');

            // Get system identity
            $identityResult = $this->mikrotikService->getSystemIdentity();
            
            if (!$identityResult['success']) {
                Log::error('Failed to get system identity', [
                    'router_id' => $router->id,
                    'error' => $identityResult['message'] ?? 'Unknown error'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $identityResult['message'] ?? 'Failed to get system identity'
                ]);
            }

            $systemData = $identityResult['data'];
            
            // Update router with system information if available
            if (isset($systemData['version'])) {
                $router->update([
                    'routeros_version' => $systemData['version'],
                    'architecture' => $systemData['architecture'] ?? null,
                    'board_name' => $systemData['board_name'] ?? null,
                    'last_system_check' => now(),
                ]);
                
                Log::info('Router system info updated in database', [
                    'router_id' => $router->id,
                    'version' => $systemData['version']
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $systemData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting system identity: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get network traffic information
     */
    public function getNetworkTraffic(Router $router)
    {
        try {
            Log::info('Getting network traffic', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'router_ip' => $router->ip_address
            ]);

            // Use trait method for connection
            $connected = $this->connectToMikrotik($router);
            
            if (!$connected) {
                Log::warning('Failed to connect to router for network traffic', [
                    'router_id' => $router->id,
                    'ip' => $router->ip_address
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to router'
                ]);
            }

            Log::info('Connected successfully, getting network traffic');

            // Get network traffic data
            $trafficResult = $this->mikrotikService->getNetworkTraffic();
            
            if (!$trafficResult['success']) {
                Log::error('Failed to get network traffic', [
                    'router_id' => $router->id,
                    'error' => $trafficResult['message'] ?? 'Unknown error'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $trafficResult['message'] ?? 'Failed to get network traffic'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $trafficResult['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting network traffic: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get RouterOS version for compatibility check
     */
    public function getRouterOSVersion(Router $router)
    {
        // Check if we have cached version info (within last 24 hours)
        if ($router->routeros_version && 
            $router->last_system_check && 
            $router->last_system_check->diffInHours(now()) < 24) {
            return [
                'version' => $router->routeros_version,
                'architecture' => $router->architecture,
                'board_name' => $router->board_name,
                'major_version' => $this->extractMajorVersion($router->routeros_version)
            ];
        }

        // Otherwise, fetch fresh data
        try {
            $api = $this->connectToRouter($router);
            if (!$api) {
                return null;
            }

            $query = new Query('/system/resource/print');
            $response = $api->query($query)->read();

            if (!empty($response)) {
                $resource = $response[0];
                $version = $resource['version'] ?? null;
                $architecture = $resource['architecture-name'] ?? null;
                $boardName = $resource['board-name'] ?? null;

                // Update router record
                $router->update([
                    'routeros_version' => $version,
                    'architecture' => $architecture,
                    'board_name' => $boardName,
                    'last_system_check' => now(),
                ]);

                return [
                    'version' => $version,
                    'architecture' => $architecture,
                    'board_name' => $boardName,
                    'major_version' => $this->extractMajorVersion($version)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error getting RouterOS version: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get gateway interface traffic data
     */
    public function getGatewayTraffic(Router $router)
    {
        try {
            Log::info('Getting gateway traffic', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'router_ip' => $router->ip_address
            ]);

            // Use trait method for connection
            $connected = $this->connectToMikrotik($router);
            
            if (!$connected) {
                Log::warning('Failed to connect to router for gateway traffic', [
                    'router_id' => $router->id,
                    'ip' => $router->ip_address
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to router'
                ]);
            }

            Log::info('Connected successfully, getting gateway traffic');

            // Get gateway traffic data
            $trafficResult = $this->mikrotikService->getGatewayTraffic();
            
            if (!$trafficResult['success']) {
                Log::error('Failed to get gateway traffic', [
                    'router_id' => $router->id,
                    'error' => $trafficResult['message'] ?? 'Unknown error'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $trafficResult['message'] ?? 'Failed to get gateway traffic'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $trafficResult['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting gateway traffic: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get ISP information for router's public IP
     */
    public function getIspInfo(Router $router)
    {
        try {
            $user = auth()->user();
            
            // Check if user has access to this router
            $isSuperAdmin = $user->role && $user->role->name === 'super_admin';
            if (!$isSuperAdmin && !$user->routers->contains($router->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this router'
                ], 403);
            }

            // Get public IP from router (like "what is my IP")
            $publicIp = $this->getRouterPublicIp($router);
            
            if (!$publicIp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine router public IP',
                    'data' => null
                ]);
            }

            // Get ISP information from BGP Tools using public IP
            $ispInfo = $this->bgpToolsService->getIspInfo($publicIp);
            
            if (!$ispInfo['success']) {
                // Fallback to whois if BGP Tools fails
                $whoisInfo = $this->bgpToolsService->getWhoisInfo($publicIp);
                if ($whoisInfo['success']) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'public_ip' => $publicIp,
                            'data_source' => 'whois',
                            'isp_name' => $whoisInfo['data']['org_name'] ?? $whoisInfo['data']['net_name'] ?? 'Unknown',
                            'asn' => isset($whoisInfo['data']['asn']) ? $whoisInfo['data']['asn'] : null,
                            'upstreams' => [],
                            'country' => $whoisInfo['data']['country'] ?? 'Unknown',
                            'last_updated' => now()->format('Y-m-d H:i:s')
                        ]
                    ]);
                }
                
                return response()->json($ispInfo);
            }

            return response()->json([
                'success' => true,
                'data' => array_merge($ispInfo['data'], [
                    'public_ip' => $publicIp,
                    'data_source' => 'bgp.tools'
                ])
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting ISP info for router ' . $router->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ISP information',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get router's public IP (like "what is my IP")
     */
    private function getRouterPublicIp(Router $router)
    {
        try {
            // Method 1: Try to get public IP through MikroTik tools
            if ($this->connectToMikrotik($router)) {
                $client = $this->mikrotikService->getClient();
                
                // Method 1a: Check if Cloud service is enabled (newer RouterOS)
                try {
                    Log::info("Method 1a: Checking cloud service for public IP");
                    $query = new Query('/ip/cloud/print');
                    $cloudInfo = $client->query($query)->read();
                    
                    Log::info("Cloud info response", ['cloud' => $cloudInfo]);
                    
                    if (!empty($cloudInfo) && isset($cloudInfo[0]['public-address'])) {
                        $publicIp = trim($cloudInfo[0]['public-address']);
                        Log::info("Found public IP in cloud service: {$publicIp}");
                        if (filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                            Log::info("Got public IP via MikroTik cloud service: {$publicIp}");
                            return $publicIp;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik cloud service method failed: " . $e->getMessage());
                }

                try {
                    Log::info("Attempting to get public IP for router {$router->id} using MikroTik fetch");
                    
                    // Method 1b: Simple fetch method (get response directly)
                    Log::info("Method 1b: Trying direct fetch response");
                    $query = new Query('/tool/fetch');
                    $query->equal('url', 'http://myip.dnsomatic.com/');
                    $query->equal('mode', 'http');
                    $response = $client->query($query)->read();
                    
                    Log::info("Direct fetch response", ['response' => $response]);
                    
                    if (!empty($response)) {
                        // Look for status and data in response
                        foreach ($response as $item) {
                            Log::info("Checking response item", ['item' => $item]);
                            if (isset($item['status']) && $item['status'] === 'finished' && isset($item['data'])) {
                                $publicIp = trim($item['data']);
                                Log::info("Found data in response: {$publicIp}");
                                if (filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                                    Log::info("Got public IP via MikroTik fetch (direct): {$publicIp}");
                                    return $publicIp;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik direct fetch method failed: " . $e->getMessage());
                }

                try {
                    // Method 1c: Use /tool/fetch with file method (simplified)
                    Log::info("Method 1c: Trying simplified file-based fetch");
                    
                    // First remove any existing file
                    try {
                        $query = new Query('/file/remove');
                        $query->equal('numbers', 'mypublicip.txt');
                        $client->query($query)->read();
                    } catch (\Exception $e) {
                        // File might not exist, that's ok
                    }
                    
                    $query = new Query('/tool/fetch');
                    $query->equal('url', 'http://myip.dnsomatic.com/');
                    $query->equal('dst-path', 'mypublicip.txt');
                    $fetchResult = $client->query($query)->read();
                    
                    Log::info("File fetch query result", ['result' => $fetchResult]);
                    
                    // Wait for file to be created and download to complete
                    Log::info("Waiting 5 seconds for file to be written");
                    sleep(5);
                    
                    // Check if file exists
                    $query = new Query('/file/print');
                    $fileList = $client->query($query)->read();
                    
                    Log::info("File list check", ['files' => $fileList]);
                    
                    foreach ($fileList as $file) {
                        if (isset($file['name']) && $file['name'] === 'mypublicip.txt') {
                            Log::info("Found file mypublicip.txt", ['file' => $file]);
                            
                            // Try to read the file using /file/get
                            try {
                                $query = new Query('/file/get');
                                $query->equal('numbers', $file['.id']);
                                $fileContent = $client->query($query)->read();
                                
                                Log::info("File content response", ['content' => $fileContent]);
                                
                                if (!empty($fileContent) && isset($fileContent[0]['contents'])) {
                                    $publicIp = trim($fileContent[0]['contents']);
                                    Log::info("Extracted IP from file: {$publicIp}");
                                    
                                    if (filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                                        Log::info("Got public IP via MikroTik fetch (file method): {$publicIp}");
                                        
                                        // Clean up the temporary file
                                        try {
                                            $query = new Query('/file/remove');
                                            $query->equal('numbers', $file['.id']);
                                            $client->query($query)->read();
                                            Log::info("Temporary file cleaned up");
                                        } catch (\Exception $e) {
                                            Log::warning("Could not remove temp file: " . $e->getMessage());
                                        }
                                        
                                        return $publicIp;
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("Error reading file content: " . $e->getMessage());
                            }
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik fetch file method failed: " . $e->getMessage());
                }

                try {
                    // Method 1d: Alternative fetch method with ipify (simplified)
                    Log::info("Method 1d: Trying ipify service");
                    
                    // Use HTTP instead of HTTPS for simplicity
                    $query = new Query('/tool/fetch');
                    $query->equal('url', 'http://ipv4.icanhazip.com/');
                    $query->equal('dst-path', 'myip.txt');
                    $fetchResult = $client->query($query)->read();
                    
                    Log::info("Ipify fetch result", ['result' => $fetchResult]);
                    
                    // Wait for file to be created
                    Log::info("Waiting 3 seconds for ipify file");
                    sleep(3);
                    
                    // Check if file exists and read it
                    $query = new Query('/file/print');
                    $fileList = $client->query($query)->read();
                    
                    foreach ($fileList as $file) {
                        if (isset($file['name']) && $file['name'] === 'myip.txt') {
                            Log::info("Found ipify file", ['file' => $file]);
                            
                            // Read the file
                            try {
                                $query = new Query('/file/get');
                                $query->equal('numbers', $file['.id']);
                                $fileContent = $client->query($query)->read();
                                
                                Log::info("Ipify file content", ['content' => $fileContent]);
                                
                                if (!empty($fileContent) && isset($fileContent[0]['contents'])) {
                                    $publicIp = trim($fileContent[0]['contents']);
                                    Log::info("Extracted IP from ipify: {$publicIp}");
                                    
                                    // Clean up the temporary file
                                    try {
                                        $query = new Query('/file/remove');
                                        $query->equal('numbers', $file['.id']);
                                        $client->query($query)->read();
                                        Log::info("Ipify temp file cleaned up");
                                    } catch (\Exception $e) {
                                        Log::warning("Could not remove ipify temp file: " . $e->getMessage());
                                    }
                                    
                                    if (filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                                        Log::info("Got public IP via MikroTik fetch (ipify): {$publicIp}");
                                        return $publicIp;
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("Error reading ipify file: " . $e->getMessage());
                            }
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik fetch method (ipify) failed: " . $e->getMessage());
                }

                try {
                    // Method 1d: Get from interface with default route (usually PPPoE/DHCP client)
                    $query = new Query('/ip/route/print');
                    $query->where('dst-address', '0.0.0.0/0');
                    $query->where('active', 'true');
                    $routes = $client->query($query)->read();
                    
                    if (!empty($routes)) {
                        // Get the interface that has default route
                        $defaultInterface = $routes[0]['interface'] ?? null;
                        
                        if ($defaultInterface) {
                            // Get IP address from that interface
                            $query = new Query('/ip/address/print');
                            $query->where('interface', $defaultInterface);
                            $addresses = $client->query($query)->read();
                            
                            foreach ($addresses as $addr) {
                                $ip = explode('/', $addr['address'])[0];
                                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                                    Log::info("Got public IP from interface {$defaultInterface}: {$ip}");
                                    return $ip;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik interface method failed: " . $e->getMessage());
                }
            }

            // Method 2: Fallback to server-side detection (less accurate but works)
            Log::info("Falling back to server-side public IP detection");
            return $this->getServerPublicIp();

        } catch (\Exception $e) {
            Log::error('Error getting public IP for router ' . $router->id . ': ' . $e->getMessage());
            return $this->getServerPublicIp(); // Ultimate fallback
        }
    }

    /**
     * Get gateway IP from router (keeping for compatibility)
     */
    private function getRouterGatewayIp(Router $router)
    {
        try {
            // First try to get from MikroTik API
            $api = $this->connectToRouter($router);
            if ($api) {
                $query = new Query('/ip/route/print');
                $query->where('dst-address', '0.0.0.0/0');
                $query->where('active', 'true');
                $routes = $api->query($query)->read();
                
                if (!empty($routes)) {
                    $gateway = $routes[0]['gateway'] ?? null;
                    if ($gateway && filter_var($gateway, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                        return $gateway;
                    }
                }
            }

            // Fallback: try to detect from common gateway detection
            $possibleGateways = [
                // Try to ping common gateway IPs to determine external gateway
                '8.8.8.8', // Google DNS
                '1.1.1.1', // Cloudflare DNS
            ];

            // For now, we'll use a simple approach - try to get public IP
            // This could be enhanced to actually trace route to find the gateway
            $publicIp = $this->getServerPublicIp();
            if ($publicIp) {
                return $publicIp;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting gateway IP for router ' . $router->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get server's public IP (fallback method)
     */
    private function getServerPublicIp()
    {
        try {
            $services = [
                'https://api.ipify.org?format=text',
                'https://ifconfig.me/ip',
                'https://icanhazip.com',
                'https://ipecho.net/plain'
            ];

            foreach ($services as $service) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5,
                            'user_agent' => 'Mozilla/5.0 (compatible; MikroTik-ISP-Monitor/1.0)'
                        ]
                    ]);
                    
                    $response = file_get_contents($service, false, $context);
                    if ($response !== false) {
                        $ip = trim($response);
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                            Log::info("Got server public IP from {$service}: {$ip}");
                            return $ip;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Service {$service} failed: " . $e->getMessage());
                    continue;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting server public IP: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract major version number from RouterOS version string
     */
    private function extractMajorVersion($version)
    {
        if (!$version) return null;
        
        // Extract major version (e.g., "7.1.5" -> "7", "6.49.1" -> "6")
        preg_match('/^(\d+)/', $version, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }
}
