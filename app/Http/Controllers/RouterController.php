<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class RouterController extends Controller
{
    use HandlesMikrotikConnection;
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
        $this->mikrotikService = $mikrotikService;
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
     * Get router status via AJAX
     */
    public function getRouterStatus(Router $router)
    {
        try {
            $statusInfo = $this->getRouterStatusInfo($router);
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
                'uptime' => 'N/A',
                'error' => 'Status check failed'
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
                    'uptime' => 'N/A',
                    'error' => $statusResult['message']
                ];
            }

            $data = $statusResult['data'];
            
            return [
                'connected' => true,
                'cpu_load' => $data['cpu_load'] . '%',
                'memory_usage' => $this->mikrotikService->formatMemorySize($data['used_memory']) . ' / ' . 
                                $this->mikrotikService->formatMemorySize($data['total_memory']),
                'memory_usage_percent' => $data['memory_usage_percent'],
                'active_ppp_sessions' => $data['active_ppp_sessions'],
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
                'uptime' => 'N/A',
                'error' => 'Connection error'
            ];
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
     * Get router interfaces information
     */
    public function getInterfaces(Router $router)
    {
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Get interfaces using Query class like in getSystemResource
            $interfaceQuery = new \RouterOS\Query('/interface/print');
            $interfaces = $client->query($interfaceQuery)->read();

            // Get interface statistics - simplified version
            $stats = [];
            try {
                $statsQuery = new \RouterOS\Query('/interface/monitor-traffic');
                $statsQuery->where('interface', 'all');
                $statsQuery->where('duration', '1');
                $stats = $client->query($statsQuery)->read();
            } catch (\Exception $e) {
                Log::warning('Could not get interface stats: ' . $e->getMessage());
                $stats = [];
            }

            return response()->json([
                'success' => true,
                'interfaces' => $interfaces,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting interfaces: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
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
     * Get system information
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
}
