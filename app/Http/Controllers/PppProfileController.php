<?php

namespace App\Http\Controllers;

use App\Models\PppProfile;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PppProfileController extends Controller
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
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role && $user->role->name === 'super_admin';
        
        // Start building the query
        $query = PppProfile::with('router');
        
        // Apply access control first
        if (!$isSuperAdmin) {
            $query->whereHas('router', function($q) use ($user) {
                $q->whereIn('id', $user->routers->pluck('id'));
            });
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Apply router filter
        if ($request->filled('router')) {
            $query->where('router_id', $request->router);
        }
        
        // Apply sync status filter
        if ($request->filled('sync_status')) {
            if ($request->sync_status === 'synced') {
                $query->where('is_synced', true);
            } elseif ($request->sync_status === 'not_synced') {
                $query->where('is_synced', false);
            }
        }
        
        $profiles = $query->latest()->paginate(15)->appends($request->query());
        $routers = $isSuperAdmin ? Router::all() : $user->routers;

        return view('ppp-profiles.index', compact('profiles', 'routers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $routers = $user->isSuperAdmin() ? Router::all() : $user->routers;
        
        return view('ppp-profiles.create', compact('routers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'router_id' => 'required|exists:routers,id',
            'local_address' => 'nullable|string|max:255',
            'remote_address' => 'nullable|string|max:255',
            'dns_server' => 'nullable|string|max:255',
            'rate_limit' => 'nullable|string|max:255',
            'session_timeout' => 'nullable|integer|min:0',
            'idle_timeout' => 'nullable|integer|min:0',
            'only_one' => 'boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                return redirect()->back()->withErrors(['router_id' => 'Unauthorized access to this router.']);
            }
        }

        // Check if profile name already exists for this router
        $existingProfile = PppProfile::where('router_id', $router->id)
            ->where('name', $request->name)
            ->first();

        if ($existingProfile) {
            return redirect()->back()->withErrors(['name' => 'Profile name already exists for this router.'])->withInput();
        }

        PppProfile::create([
            'name' => $request->name,
            'router_id' => $request->router_id,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'dns_server' => $request->dns_server,
            'rate_limit' => $request->rate_limit,
            'session_timeout' => $request->session_timeout,
            'idle_timeout' => $request->idle_timeout,
            'only_one' => $request->boolean('only_one'),
            'comment' => $request->comment,
            'created_by' => $user->id,
        ]);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($pppProfile->router_id)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return view('ppp-profiles.show', compact('pppProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($pppProfile->router_id)) {
                abort(403, 'Unauthorized access.');
            }
        }

        $routers = $user->isSuperAdmin() ? Router::all() : $user->routers;
        
        return view('ppp-profiles.edit', compact('pppProfile', 'routers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PppProfile $pppProfile)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'router_id' => 'required|exists:routers,id',
            'local_address' => 'nullable|string|max:255',
            'remote_address' => 'nullable|string|max:255',
            'dns_server' => 'nullable|string|max:255',
            'rate_limit' => 'nullable|string|max:255',
            'session_timeout' => 'nullable|integer|min:0',
            'idle_timeout' => 'nullable|integer|min:0',
            'only_one' => 'boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($pppProfile->router_id) || !$user->routers->contains($router->id)) {
                abort(403, 'Unauthorized access.');
            }
        }

        // Check if profile name already exists for this router (excluding current profile)
        $existingProfile = PppProfile::where('router_id', $router->id)
            ->where('name', $request->name)
            ->where('id', '!=', $pppProfile->id)
            ->first();

        if ($existingProfile) {
            return redirect()->back()->withErrors(['name' => 'Profile name already exists for this router.'])->withInput();
        }

        $pppProfile->update([
            'name' => $request->name,
            'router_id' => $request->router_id,
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'dns_server' => $request->dns_server,
            'rate_limit' => $request->rate_limit,
            'session_timeout' => $request->session_timeout,
            'idle_timeout' => $request->idle_timeout,
            'only_one' => $request->boolean('only_one'),
            'comment' => $request->comment,
        ]);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($pppProfile->router_id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $pppProfile->delete();

        return response()->json([
            'success' => true,
            'message' => 'PPP Profile deleted successfully.'
        ]);
    }

    /**
     * Sync profile to MikroTik
     */
    public function syncToMikrotik(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($pppProfile->router_id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($pppProfile->router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        try {
            $client = $this->mikrotikService->getClient();
            
            // Prepare profile data for MikroTik
            $profileData = [
                'name' => $pppProfile->name,
            ];

            if ($pppProfile->local_address) {
                $profileData['local-address'] = $pppProfile->local_address;
            }
            if ($pppProfile->remote_address) {
                $profileData['remote-address'] = $pppProfile->remote_address;
            }
            if ($pppProfile->dns_server) {
                $profileData['dns-server'] = $pppProfile->dns_server;
            }
            if ($pppProfile->rate_limit) {
                $profileData['rate-limit'] = $pppProfile->rate_limit;
            }
            if ($pppProfile->session_timeout) {
                $profileData['session-timeout'] = $pppProfile->session_timeout;
            }
            if ($pppProfile->idle_timeout) {
                $profileData['idle-timeout'] = $pppProfile->idle_timeout;
            }
            if ($pppProfile->only_one) {
                $profileData['only-one'] = 'yes';
            }
            if ($pppProfile->comment) {
                $profileData['comment'] = $pppProfile->comment;
            }

            // Check if profile exists in MikroTik
            if ($pppProfile->mikrotik_id) {
                // Update existing profile
                $query = $client->query('/ppp/profile/set');
                $query->where('.id', $pppProfile->mikrotik_id);
                foreach ($profileData as $key => $value) {
                    if ($key !== 'name') { // Don't update name
                        $query->where($key, $value);
                    }
                }
                $client->query($query)->read();
            } else {
                // Add new profile
                $query = $client->query('/ppp/profile/add');
                foreach ($profileData as $key => $value) {
                    $query->where($key, $value);
                }
                $response = $client->query($query)->read();
                
                // Update mikrotik_id if returned
                if (isset($response[0]['ret'])) {
                    $pppProfile->update(['mikrotik_id' => $response[0]['ret']]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile synced to MikroTik successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing profile to MikroTik: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing profile to MikroTik: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Import profiles from MikroTik router (using same pattern as PPP Secret)
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
            Log::error('Failed to connect to MikroTik router for PPP profiles', [
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'username' => $router->username,
                'port' => $router->port
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        // Get PPP profiles from MikroTik using direct client (same pattern as PPP secrets)
        try {
            $client = $this->mikrotikService->getClient();
            $mikrotikProfiles = $client->query('/ppp/profile/print')->read();

            Log::info("Import from MikroTik: Got " . count($mikrotikProfiles) . " profiles from MikroTik");

            $importedCount = 0;
            $skippedCount = 0;

            foreach ($mikrotikProfiles as $mikrotikProfile) {
                try {
                    // Skip if profile already exists in database
                    $existingProfile = PppProfile::where('router_id', $router->id)
                        ->where('name', $mikrotikProfile['name'])
                        ->first();

                    if ($existingProfile) {
                        $skippedCount++;
                        Log::info("Profile skipped (already exists in database)", [
                            'profile_name' => $mikrotikProfile['name'],
                            'router_id' => $router->id,
                            'existing_profile_id' => $existingProfile->id
                        ]);
                        continue;
                    }

                    // Create new profile in database
                    $newProfile = PppProfile::create([
                        'name' => $mikrotikProfile['name'],
                        'router_id' => $router->id,
                        'local_address' => $mikrotikProfile['local-address'] ?? null,
                        'remote_address' => $mikrotikProfile['remote-address'] ?? null,
                        'dns_server' => $mikrotikProfile['dns-server'] ?? null,
                        'rate_limit' => $mikrotikProfile['rate-limit'] ?? null,
                        'session_timeout' => isset($mikrotikProfile['session-timeout']) ? (int)$mikrotikProfile['session-timeout'] : null,
                        'idle_timeout' => isset($mikrotikProfile['idle-timeout']) ? (int)$mikrotikProfile['idle-timeout'] : null,
                        'only_one' => isset($mikrotikProfile['only-one']) && $mikrotikProfile['only-one'] === 'yes',
                        'comment' => $mikrotikProfile['comment'] ?? null,
                        'mikrotik_id' => $mikrotikProfile['.id'],
                        'created_by' => $user->id,
                    ]);

                    $importedCount++;
                    
                    Log::info("Profile imported successfully", [
                        'profile_name' => $mikrotikProfile['name'],
                        'profile_id' => $newProfile->id,
                        'router_id' => $router->id
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error("Failed to import profile", [
                        'profile_name' => $mikrotikProfile['name'] ?? 'unknown',
                        'router_id' => $router->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other profiles even if one fails
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Import completed. {$importedCount} profiles imported, {$skippedCount} skipped (already exists).",
                'imported' => $importedCount,
                'skipped' => $skippedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error importing PPP profiles from MikroTik: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing PPP profiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview profiles from MikroTik router before import (using same pattern as PPP Secret)
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
        Log::info('Attempting to connect to MikroTik for PPP profiles preview import', [
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
            Log::error('Failed to connect to MikroTik router for PPP profiles', [
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'username' => $router->username,
                'port' => $router->port
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        // Get PPP profiles from MikroTik using direct client (same pattern as PPP secrets)
        try {
            $client = $this->mikrotikService->getClient();
            $mikrotikProfiles = $client->query('/ppp/profile/print')->read();

            Log::info("Preview import: Got " . count($mikrotikProfiles) . " profiles from MikroTik");

            $previewData = [];
            $existingCount = 0;
            $newCount = 0;

            foreach ($mikrotikProfiles as $mikrotikProfile) {
                // Check if profile already exists in database
                $existingByName = PppProfile::where('router_id', $router->id)
                    ->where('name', $mikrotikProfile['name'])
                    ->first();

                // Also check if profile with same mikrotik_id exists
                $existingByMikrotikId = null;
                if (isset($mikrotikProfile['.id'])) {
                    $existingByMikrotikId = PppProfile::where('router_id', $router->id)
                        ->where('mikrotik_id', $mikrotikProfile['.id'])
                        ->first();
                }

                // Determine status
                $status = 'new';
                $statusDetail = '';
                
                if ($existingByName && $existingByMikrotikId && $existingByName->id === $existingByMikrotikId->id) {
                    $status = 'exists';
                    $statusDetail = 'Profile already exists in database';
                    $existingCount++;
                } elseif ($existingByName) {
                    $status = 'name_conflict';
                    $statusDetail = 'Profile name already exists with different MikroTik ID';
                    $existingCount++;
                } elseif ($existingByMikrotikId) {
                    $status = 'id_conflict';
                    $statusDetail = 'MikroTik ID already exists with different name';
                    $existingCount++;
                } else {
                    $newCount++;
                }

                $previewData[] = [
                    'name' => $mikrotikProfile['name'],
                    'local_address' => $mikrotikProfile['local-address'] ?? null,
                    'remote_address' => $mikrotikProfile['remote-address'] ?? null,
                    'dns_server' => $mikrotikProfile['dns-server'] ?? null,
                    'rate_limit' => $mikrotikProfile['rate-limit'] ?? null,
                    'session_timeout' => $mikrotikProfile['session-timeout'] ?? null,
                    'idle_timeout' => $mikrotikProfile['idle-timeout'] ?? null,
                    'only_one' => isset($mikrotikProfile['only-one']) && $mikrotikProfile['only-one'] === 'yes',
                    'comment' => $mikrotikProfile['comment'] ?? null,
                    'mikrotik_id' => $mikrotikProfile['.id'],
                    'status' => $status,
                    'status_detail' => $statusDetail,
                    'can_import' => $status === 'new'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'summary' => [
                    'total' => count($previewData),
                    'new' => $newCount,
                    'existing' => $existingCount
                ],
                'router' => [
                    'name' => $router->name,
                    'ip' => $router->ip_address
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting PPP profiles from MikroTik: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting PPP profiles from MikroTik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import selected profiles from preview (using same pattern as PPP Secret)
     */
    public function importSelected(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'profiles' => 'required|array',
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!$user->isSuperAdmin()) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($request->profiles as $profileData) {
            try {
                // Check if profile already exists by name
                $existingByName = PppProfile::where('router_id', $router->id)
                    ->where('name', $profileData['name'])
                    ->first();

                // Check if profile already exists by mikrotik_id
                $existingByMikrotikId = null;
                if (isset($profileData['mikrotik_id'])) {
                    $existingByMikrotikId = PppProfile::where('router_id', $router->id)
                        ->where('mikrotik_id', $profileData['mikrotik_id'])
                        ->first();
                }

                // Skip if already exists
                if ($existingByName || $existingByMikrotikId) {
                    $skippedCount++;
                    Log::info("Profile skipped during import (already exists)", [
                        'profile_name' => $profileData['name'],
                        'router_id' => $router->id,
                        'reason' => $existingByName ? 'name_exists' : 'mikrotik_id_exists'
                    ]);
                    continue; // Skip existing profiles
                }

                // Create new profile in database
                $newProfile = PppProfile::create([
                    'name' => $profileData['name'],
                    'router_id' => $router->id,
                    'local_address' => $profileData['local_address'] ?? null,
                    'remote_address' => $profileData['remote_address'] ?? null,
                    'dns_server' => $profileData['dns_server'] ?? null,
                    'rate_limit' => $profileData['rate_limit'] ?? null,
                    'session_timeout' => isset($profileData['session_timeout']) ? (int)$profileData['session_timeout'] : null,
                    'idle_timeout' => isset($profileData['idle_timeout']) ? (int)$profileData['idle_timeout'] : null,
                    'only_one' => ($profileData['only_one'] ?? 'false') === 'true',
                    'comment' => $profileData['comment'] ?? null,
                    'mikrotik_id' => $profileData['mikrotik_id'] ?? $profileData['name'],
                    'created_by' => $user->id,
                ]);

                $importedCount++;
                
                Log::info("Profile imported successfully from selected", [
                    'profile_name' => $profileData['name'],
                    'profile_id' => $newProfile->id,
                    'router_id' => $router->id
                ]);

            } catch (\Exception $e) {
                $errorMessage = "Failed to import profile '{$profileData['name']}': " . $e->getMessage();
                $errors[] = $errorMessage;
                
                Log::error("Failed to import selected profile", [
                    'profile_name' => $profileData['name'] ?? 'unknown',
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed successfully. {$importedCount} profiles imported, {$skippedCount} skipped.",
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ]);
    }
}
