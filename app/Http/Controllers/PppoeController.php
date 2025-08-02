<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPppoe;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class PppoeController extends Controller
{
    use HandlesMikrotikConnection;
    
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,admin');
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            $pppoeSecrets = UserPppoe::with(['user', 'router'])
                ->latest()
                ->paginate(15);
        } else {
            // Regular admin can only see their own PPPoE secrets and only from assigned routers
            $userRouterIds = $user->routers->pluck('id');
            $pppoeSecrets = UserPppoe::with(['router'])
                ->where('user_id', $user->id)
                ->whereIn('router_id', $userRouterIds)
                ->latest()
                ->paginate(15);
        }
        
        return view('pppoe.index', compact('pppoeSecrets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            $routers = Router::where('status', 'active')->get();
        } else {
            $routers = $user->routers()->where('status', 'active')->get();
        }
        
        return view('pppoe.create', compact('routers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'service' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:255',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|ip',
            'comment' => 'nullable|string|max:500',
            'disabled' => 'nullable|boolean',
        ]);

        $router = Router::findOrFail($request->router_id);
        
        // Check if user has access to this router (for non-super admin)
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                abort(403, 'Anda tidak memiliki akses ke router ini.');
            }
        }

        // Check for duplicate username on the same router
        $existingSecret = UserPppoe::where('router_id', $router->id)
            ->where('username', $request->username)
            ->first();
        
        if ($existingSecret) {
            return back()->withErrors(['username' => 'Username sudah ada pada router ini.'])->withInput();
        }

        // Connect to MikroTik and create PPPoE secret
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return back()->withErrors(['connection' => 'Gagal terhubung ke router.'])->withInput();
        }

        $mikrotikData = [
            'username' => $request->username,
            'password' => $request->password,
            'service' => $request->service ?: 'pppoe',
            'profile' => $request->profile,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'comment' => $request->comment,
            'disabled' => $request->boolean('disabled'),
        ];

        $result = $this->mikrotikService->createPppSecret($mikrotikData);

        if (!$result['success']) {
            return back()->withErrors(['mikrotik' => $result['message']])->withInput();
        }

        // Save to database
        UserPppoe::create([
            'user_id' => $user->id,
            'router_id' => $router->id,
            'username' => $request->username,
            'password' => $request->password,
            'service' => $request->service ?: 'pppoe',
            'profile' => $request->profile,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'comment' => $request->comment,
            'disabled' => $request->boolean('disabled'),
            'mikrotik_id' => $result['id'],
        ]);

        return redirect()->route('pppoe.index')
            ->with('success', 'PPPoE secret berhasil dibuat di MikroTik dan disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke PPPoE secret ini.');
        }
        
        $pppoe->load(['user', 'router']);
        return view('pppoe.show', compact('pppoe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit PPPoE secret ini.');
        }
        
        if ($user->isSuperAdmin()) {
            $routers = Router::where('status', 'active')->get();
        } else {
            $routers = $user->routers()->where('status', 'active')->get();
        }
        
        return view('pppoe.edit', compact('pppoe', 'routers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit PPPoE secret ini.');
        }
        
        $request->validate([
            'password' => 'nullable|string|min:6',
            'service' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:255',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|ip',
            'comment' => 'nullable|string|max:500',
            'disabled' => 'nullable|boolean',
        ]);

        $router = $pppoe->router;

        // Connect to MikroTik and update PPPoE secret
        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return back()->withErrors(['connection' => 'Gagal terhubung ke router.'])->withInput();
        }

        $mikrotikData = array_filter([
            'password' => $request->password,
            'service' => $request->service,
            'profile' => $request->profile,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'comment' => $request->comment,
            'disabled' => $request->boolean('disabled'),
        ]);

        if (!empty($mikrotikData)) {
            $result = $this->mikrotikService->updatePppSecret($pppoe->mikrotik_id, $mikrotikData);

            if (!$result['success']) {
                return back()->withErrors(['mikrotik' => $result['message']])->withInput();
            }
        }

        // Update database
        $updateData = array_filter([
            'password' => $request->password,
            'service' => $request->service,
            'profile' => $request->profile,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'comment' => $request->comment,
            'disabled' => $request->boolean('disabled'),
        ]);

        $pppoe->update($updateData);

        return redirect()->route('pppoe.index')
            ->with('success', 'PPPoE secret berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus PPPoE secret ini.');
        }

        $router = $pppoe->router;

        // Connect to MikroTik and delete PPPoE secret
        $connected = $this->connectToMikrotik($router);

        if ($connected && $pppoe->mikrotik_id) {
            $this->mikrotikService->deletePppSecret($pppoe->mikrotik_id);
        }

        $pppoe->delete();
        
        return redirect()->route('pppoe.index')
            ->with('success', 'PPPoE secret berhasil dihapus.');
    }

    /**
     * Get PPP profiles from router
     */
    public function getProfiles(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
        ]);

        try {
            $user = auth()->user();
            $router = Router::findOrFail($request->router_id);
            
            Log::info('Attempting to get profiles from router', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'ip_address' => $router->ip_address,
                'username' => $router->username,
                'port' => $router->port
            ]);
            
            // Check access permissions
            if (!$user->isSuperAdmin()) {
                if (!$user->routers->contains($router->id)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
                }
            }

            $connected = $this->connectToMikrotik($router);

            if (!$connected) {
                Log::error('Failed to connect to router for profiles', [
                    'router_id' => $router->id,
                    'ip_address' => $router->ip_address
                ]);
                return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
            }

            $result = $this->mikrotikService->getPppProfiles();
            
            Log::info('Profile fetch result', [
                'success' => $result['success'],
                'profile_count' => $result['success'] ? count($result['data']) : 0
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to get profiles: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load profiles.']);
        }
    }

    /**
     * Sync PPPoE secret to MikroTik
     */
    public function syncToMikrotik(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $router = $pppoe->router;

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        $mikrotikData = [
            'username' => $pppoe->username,
            'password' => $pppoe->password,
            'service' => $pppoe->service,
            'profile' => $pppoe->profile,
            'local_address' => $pppoe->local_address,
            'remote_address' => $pppoe->remote_address,
            'comment' => $pppoe->comment,
            'disabled' => $pppoe->disabled,
        ];

        if ($pppoe->mikrotik_id) {
            // Update existing secret
            $result = $this->mikrotikService->updatePppSecret($pppoe->mikrotik_id, $mikrotikData);
        } else {
            // Create new secret
            $result = $this->mikrotikService->createPppSecret($mikrotikData);
            if ($result['success'] && isset($result['id'])) {
                $pppoe->update(['mikrotik_id' => $result['id']]);
            }
        }

        return response()->json($result);
    }

    /**
     * Show PPPoE secret password
     */
    public function showPassword(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'success' => true,
            'password' => $pppoe->password
        ]);
    }

    /**
     * Get PPPoE connection statistics from MikroTik
     */
    public function getStats(UserPppoe $pppoe)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin() && $pppoe->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $router = $pppoe->router;

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        // Get active PPP sessions
        try {
            $client = $this->mikrotikService->getClient();
            $activeSessions = $client->query('/ppp/active/print', ['name' => $pppoe->username])->read();
            
            if (empty($activeSessions)) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No active connection found'
                ]);
            }
            
            $session = $activeSessions[0];
            
            $stats = [
                'status' => 'Connected',
                'uptime' => $session['uptime'] ?? 'N/A',
                'bytes_in' => isset($session['bytes-in']) ? $this->formatBytes($session['bytes-in']) : 'N/A',
                'bytes_out' => isset($session['bytes-out']) ? $this->formatBytes($session['bytes-out']) : 'N/A',
                'address' => $session['address'] ?? 'N/A',
                'caller_id' => $session['caller-id'] ?? 'N/A',
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check real-time sync status from MikroTik
     */
    public function checkSyncStatus(UserPppoe $pppoe)
    {
        Log::info('checkSyncStatus method called', ['pppoe_id' => $pppoe->id, 'username' => $pppoe->username]);
        
        $user = auth()->user();
        
        if (!$user) {
            Log::error('No authenticated user found');
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        
        // Get fresh user instance with role
        $user = \App\Models\User::with('role')->find($user->id);
        
        Log::info('User info', [
            'user_id' => $user->id, 
            'user_role' => $user->role ? $user->role->name : 'no role',
            'has_isSuperAdmin' => method_exists($user, 'isSuperAdmin'),
            'user_class' => get_class($user)
        ]);

        // Check access permissions using role check
        $isSuperAdmin = $user->role && $user->role->name === 'super_admin';
        if (!$isSuperAdmin && $pppoe->user_id !== $user->id) {
            Log::warning('Unauthorized access to sync status', ['user_id' => $user->id, 'pppoe_user_id' => $pppoe->user_id]);
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $router = $pppoe->router;
        Log::info('Attempting to check sync status', ['router_id' => $router->id, 'router_ip' => $router->ip_address]);

        try {
            $connected = $this->connectToMikrotik($router);

            Log::info('MikroTik connection result', ['connected' => $connected]);

            if (!$connected) {
                Log::error('Failed to connect to MikroTik for sync check');
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to connect to MikroTik router.',
                    'sync_status' => 'connection_failed'
                ]);
            }

            // Get all PPP secrets from MikroTik and search for the username
            try {
                Log::info('Attempting to get PPP secrets from MikroTik');
                $result = $this->mikrotikService->getPppSecrets();
                Log::info('PPP secrets result', ['success' => $result['success'], 'data_count' => $result['success'] ? count($result['data']) : 0]);
                
                if (!$result['success']) {
                    Log::error("Failed to get PPP secrets: " . $result['message']);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to get PPP secrets: ' . $result['message'],
                        'sync_status' => 'error'
                    ], 500);
                }
                
                // Search for the specific secret by username
                $secrets = [];
                Log::info('Searching for username in PPP secrets', ['target_username' => $pppoe->username]);
                foreach ($result['data'] as $secret) {
                    if (isset($secret['name']) && $secret['name'] === $pppoe->username) {
                        $secrets[] = $secret;
                        Log::info('Found matching secret', ['secret_data' => $secret]);
                        break;
                    }
                }
                Log::info('Search completed', ['found_secrets_count' => count($secrets)]);
            } catch (Exception $queryException) {
                Log::error("Error querying MikroTik for sync check: " . $queryException->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error querying MikroTik: ' . $queryException->getMessage(),
                    'sync_status' => 'error'
                ], 500);
            }

            if (empty($secrets)) {
                // Secret not found in MikroTik
                return response()->json([
                    'success' => true,
                    'message' => 'PPPoE secret not found in MikroTik router.',
                    'sync_status' => 'not_synced',
                    'mikrotik_data' => null
                ]);
            }

            $mikrotikSecret = $secrets[0];
            
            // Compare data to see if it's in sync
            $isInSync = true;
            $differences = [];

            // Check if passwords match (if we can decrypt the stored password)
            if (isset($mikrotikSecret['password']) && $mikrotikSecret['password'] !== $pppoe->password) {
                $isInSync = false;
                $differences[] = 'password';
            }

            // Check profile
            if (isset($mikrotikSecret['profile']) && $mikrotikSecret['profile'] !== $pppoe->profile) {
                $isInSync = false;
                $differences[] = 'profile';
            }

            // Check local address
            if (isset($mikrotikSecret['local-address']) && $mikrotikSecret['local-address'] !== $pppoe->local_address) {
                $isInSync = false;
                $differences[] = 'local_address';
            }

            // Check remote address
            if (isset($mikrotikSecret['remote-address']) && $mikrotikSecret['remote-address'] !== $pppoe->remote_address) {
                $isInSync = false;
                $differences[] = 'remote_address';
            }

            // Check service
            if (isset($mikrotikSecret['service']) && $mikrotikSecret['service'] !== $pppoe->service) {
                $isInSync = false;
                $differences[] = 'service';
            }

            // Check disabled status
            $mikrotikDisabled = isset($mikrotikSecret['disabled']) && $mikrotikSecret['disabled'] === 'true';
            if ($mikrotikDisabled !== $pppoe->disabled) {
                $isInSync = false;
                $differences[] = 'disabled_status';
            }

            // Update local mikrotik_id if not set but exists in MikroTik
            if (!$pppoe->mikrotik_id && isset($mikrotikSecret['.id'])) {
                $pppoe->mikrotik_id = $mikrotikSecret['.id'];
                $pppoe->save();
            }

            return response()->json([
                'success' => true,
                'message' => $isInSync ? 'PPPoE secret is synchronized with MikroTik.' : 'PPPoE secret has differences with MikroTik.',
                'sync_status' => $isInSync ? 'synced' : 'out_of_sync',
                'differences' => $differences,
                'mikrotik_data' => [
                    'id' => $mikrotikSecret['.id'] ?? null,
                    'name' => $mikrotikSecret['name'] ?? null,
                    'profile' => $mikrotikSecret['profile'] ?? null,
                    'local_address' => $mikrotikSecret['local-address'] ?? null,
                    'remote_address' => $mikrotikSecret['remote-address'] ?? null,
                    'service' => $mikrotikSecret['service'] ?? null,
                    'disabled' => isset($mikrotikSecret['disabled']) && $mikrotikSecret['disabled'] === 'true',
                    'comment' => $mikrotikSecret['comment'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking sync status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking sync status: ' . $e->getMessage(),
                'sync_status' => 'error'
            ], 500);
        }
    }

    /**
     * Preview PPPoE secrets from MikroTik router before import
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        // Log connection attempt for debugging
        Log::info('Attempting to connect to MikroTik for PPPoE secrets preview import', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'ip_address' => $router->ip_address,
            'username' => $router->username,
            'port' => $router->port,
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            Log::error('Failed to connect to MikroTik router for PPPoE secrets', [
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'username' => $router->username,
                'port' => $router->port
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        // Get PPP secrets from MikroTik
        try {
            $client = $this->mikrotikService->getClient();
            $mikrotikSecrets = $client->query('/ppp/secret/print')->read();

            Log::info("Preview import: Got " . count($mikrotikSecrets) . " secrets from MikroTik");

            $previewData = [];
            $existingCount = 0;
            $newCount = 0;

            foreach ($mikrotikSecrets as $index => $mikrotikSecret) {
                // Log each secret for debugging
                Log::debug("Preview secret at index {$index}: " . json_encode($mikrotikSecret));
                
                // Skip if no username
                if (!isset($mikrotikSecret['name']) || empty($mikrotikSecret['name'])) {
                    Log::warning("Skipping secret at index {$index} - no username");
                    continue;
                }

                // Check if secret already exists in database
                $existingSecret = UserPppoe::where('router_id', $router->id)
                    ->where('username', $mikrotikSecret['name'])
                    ->first();

                $status = $existingSecret ? 'exists' : 'new';
                if ($existingSecret) {
                    $existingCount++;
                } else {
                    $newCount++;
                }

                $previewData[] = [
                    'id' => $mikrotikSecret['.id'] ?? null,
                    'username' => $mikrotikSecret['name'] ?? '',
                    'password' => $mikrotikSecret['password'] ?? '',
                    'service' => $mikrotikSecret['service'] ?? 'any',
                    'profile' => $mikrotikSecret['profile'] ?? null,
                    'local_address' => $mikrotikSecret['local-address'] ?? null,
                    'remote_address' => $mikrotikSecret['remote-address'] ?? null,
                    'disabled' => isset($mikrotikSecret['disabled']) && $mikrotikSecret['disabled'] === 'true',
                    'comment' => $mikrotikSecret['comment'] ?? null,
                    'status' => $status,
                    'existing_id' => $existingSecret ? $existingSecret->id : null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'summary' => [
                    'total' => count($previewData),
                    'new' => $newCount,
                    'existing' => $existingCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting PPPoE secrets from MikroTik: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting PPPoE secrets from MikroTik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import selected PPPoE secrets from MikroTik
     */
    public function importSelected(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'selected_secrets' => 'required|array|min:1',
            'selected_secrets.*' => 'required|string',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($request->selected_secrets as $secretId) {
                try {
                    // Log the secret ID being processed
                    Log::info("Processing secret ID: {$secretId}");

                    // Get the specific secret from MikroTik - try multiple approaches
                    $mikrotikSecrets = [];
                    
                    // First approach: Get all secrets and filter
                    try {
                        $allSecrets = $client->query('/ppp/secret/print')->read();
                        Log::info("Got " . count($allSecrets) . " total secrets from MikroTik");
                        
                        foreach ($allSecrets as $secret) {
                            if (isset($secret['.id']) && $secret['.id'] === $secretId) {
                                $mikrotikSecrets[] = $secret;
                                Log::info("Found matching secret by ID: " . json_encode($secret));
                                break;
                            }
                        }
                    } catch (Exception $queryException) {
                        Log::error("Error querying all secrets from MikroTik: " . $queryException->getMessage());
                        $errors[] = "Error querying secret {$secretId}: " . $queryException->getMessage();
                        continue;
                    }
                    
                    // Log the response
                    Log::info("Final secret data for ID {$secretId}: " . json_encode($mikrotikSecrets));
                    
                    if (empty($mikrotikSecrets)) {
                        $errors[] = "Secret with ID {$secretId} not found in MikroTik (empty response)";
                        Log::warning("Empty response for secret ID: {$secretId}");
                        continue;
                    }

                    if (!is_array($mikrotikSecrets)) {
                        $errors[] = "Secret with ID {$secretId} returned invalid response format";
                        Log::error("Invalid response format for secret ID {$secretId}: " . gettype($mikrotikSecrets));
                        continue;
                    }

                    if (!isset($mikrotikSecrets[0])) {
                        $errors[] = "Secret with ID {$secretId} not found in MikroTik (no data)";
                        Log::warning("No data at index 0 for secret ID: {$secretId}");
                        continue;
                    }

                    $mikrotikSecret = $mikrotikSecrets[0];
                    
                    // Log the secret data
                    Log::info("Secret data for ID {$secretId}: " . json_encode($mikrotikSecret));
                    
                    // Validate required fields
                    if (!isset($mikrotikSecret['name']) || empty($mikrotikSecret['name'])) {
                        $errors[] = "Secret with ID {$secretId} has no username";
                        Log::warning("No username for secret ID: {$secretId}");
                        continue;
                    }

                    // Check if secret already exists
                    $existingSecret = UserPppoe::where('router_id', $router->id)
                        ->where('username', $mikrotikSecret['name'])
                        ->first();

                    if ($existingSecret) {
                        $skippedCount++;
                        Log::info("Secret {$mikrotikSecret['name']} already exists, skipping");
                        continue;
                    }

                    // Determine user assignment (Super Admin can assign to any user, Admin only to themselves)
                    $assignedUserId = $user->hasRole('super_admin') ? $user->id : $user->id;

                    // Create new PPPoE secret in database
                    $newSecret = UserPppoe::create([
                        'username' => $mikrotikSecret['name'],
                        'password' => $mikrotikSecret['password'] ?? '',
                        'service' => $mikrotikSecret['service'] ?? 'any',
                        'profile' => $mikrotikSecret['profile'] ?? null,
                        'local_address' => $mikrotikSecret['local-address'] ?? null,
                        'remote_address' => $mikrotikSecret['remote-address'] ?? null,
                        'disabled' => isset($mikrotikSecret['disabled']) && $mikrotikSecret['disabled'] === 'true',
                        'comment' => $mikrotikSecret['comment'] ?? null,
                        'mikrotik_id' => $mikrotikSecret['.id'] ?? null,
                        'router_id' => $router->id,
                        'user_id' => $assignedUserId,
                        'created_by' => $user->id,
                    ]);

                    $importedCount++;
                    Log::info("Successfully imported secret: {$mikrotikSecret['name']}");

                } catch (\Exception $e) {
                    $errorMessage = "Error importing secret {$secretId}: " . $e->getMessage();
                    $errors[] = $errorMessage;
                    Log::error('Error importing PPPoE secret', [
                        'secret_id' => $secretId,
                        'router_id' => $router->id,
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine()
                    ]);
                }
            }

            $message = "Import completed. {$importedCount} secrets imported";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} skipped (already exists)";
            }
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " errors occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing PPPoE secrets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing PPPoE secrets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import all PPPoE secrets from MikroTik (direct import without preview)
     */
    public function importFromMikrotik(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            $mikrotikSecrets = $client->query('/ppp/secret/print')->read();

            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($mikrotikSecrets as $index => $mikrotikSecret) {
                try {
                    // Log processing
                    Log::info("Processing secret at index {$index}: " . json_encode($mikrotikSecret));
                    
                    // Validate required fields
                    if (!isset($mikrotikSecret['name']) || empty($mikrotikSecret['name'])) {
                        $errors[] = "Found secret without username at index {$index}, skipping";
                        Log::warning("Secret without username at index {$index}");
                        continue;
                    }

                    // Check if secret already exists
                    $existingSecret = UserPppoe::where('router_id', $router->id)
                        ->where('username', $mikrotikSecret['name'])
                        ->first();

                    if ($existingSecret) {
                        $skippedCount++;
                        continue;
                    }

                    // Determine user assignment
                    $assignedUserId = $user->hasRole('super_admin') ? $user->id : $user->id;

                    // Create new PPPoE secret in database
                    UserPppoe::create([
                        'username' => $mikrotikSecret['name'],
                        'password' => $mikrotikSecret['password'] ?? '',
                        'service' => $mikrotikSecret['service'] ?? 'any',
                        'profile' => $mikrotikSecret['profile'] ?? null,
                        'local_address' => $mikrotikSecret['local-address'] ?? null,
                        'remote_address' => $mikrotikSecret['remote-address'] ?? null,
                        'disabled' => isset($mikrotikSecret['disabled']) && $mikrotikSecret['disabled'] === 'true',
                        'comment' => $mikrotikSecret['comment'] ?? null,
                        'mikrotik_id' => $mikrotikSecret['.id'] ?? null,
                        'router_id' => $router->id,
                        'user_id' => $assignedUserId,
                        'created_by' => $user->id,
                    ]);

                    $importedCount++;
                    Log::info("Successfully imported secret: {$mikrotikSecret['name']}");

                } catch (\Exception $e) {
                    $secretName = isset($mikrotikSecret['name']) ? $mikrotikSecret['name'] : 'Unknown';
                    $errorMessage = "Error importing secret {$secretName}: " . $e->getMessage();
                    $errors[] = $errorMessage;
                    Log::error('Error importing PPPoE secret', [
                        'secret_name' => $secretName,
                        'router_id' => $router->id,
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine(),
                        'secret_data' => json_encode($mikrotikSecret ?? 'null')
                    ]);
                }
            }

            $message = "Import completed. {$importedCount} secrets imported";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} skipped (already exists)";
            }
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " errors occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing all PPPoE secrets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing PPPoE secrets: ' . $e->getMessage()
            ], 500);
        }
    }
}
