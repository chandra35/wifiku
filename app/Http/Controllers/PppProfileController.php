<?php

namespace App\Http\Controllers;

use App\Models\PppProfile;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Traits\HandlesMikrotikConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

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
            // Non-super_admin can only see profiles they created themselves
            $query->where('created_by', $user->id);
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
        $routers = ($user->role && $user->role->name === 'super_admin') ? Router::all() : $user->routers;
        
        return view('ppp-profiles.create', compact('routers'));
    }

    /**
     * Get IP pools from MikroTik router
     */
    public function getIpPools(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id'
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        $result = $this->mikrotikService->getIpPools();

        return response()->json($result);
    }

    /**
     * Create new IP pool in MikroTik router
     */
    public function createIpPool(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'name' => 'required|string|max:255',
            'ranges' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        $result = $this->mikrotikService->createIpPool($request->name, $request->ranges);

        return response()->json($result);
    }

    /**
     * Delete IP pool from MikroTik router
     */
    public function deleteIpPool(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'pool_name' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        $router = Router::findOrFail($request->router_id);
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            if (!$user->routers->contains($router->id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        $result = $this->mikrotikService->deleteIpPool($request->pool_name);

        return response()->json($result);
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
        if (!($user->role && $user->role->name === 'super_admin')) {
            if (!$user->routers->contains($router->id)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this router.',
                        'errors' => ['router_id' => ['Unauthorized access to this router.']]
                    ], 403);
                }
                return redirect()->back()->withErrors(['router_id' => 'Unauthorized access to this router.']);
            }
        }

        // Check if profile name already exists for this router
        $existingProfile = PppProfile::where('router_id', $router->id)
            ->where('name', $request->name)
            ->first();

        if ($existingProfile) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile name already exists for this router.',
                    'errors' => ['name' => ['Profile name already exists for this router.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['name' => 'Profile name already exists for this router.'])->withInput();
        }

        $pppProfile = PppProfile::create([
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
            'is_synced' => false,
            'created_by' => $user->id,
        ]);

        // Return JSON response for AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'PPP Profile created successfully. You can sync it to MikroTik manually when ready.',
                'data' => $pppProfile
            ]);
        }

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile created successfully. You can sync it to MikroTik manually when ready.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            // Non-super_admin can only view profiles they created
            if ($pppProfile->created_by !== $user->id) {
                abort(403, 'You can only view PPP Profiles you created.');
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
        if (!($user->role && $user->role->name === 'super_admin')) {
            // Non-super_admin can only edit profiles they created
            if ($pppProfile->created_by !== $user->id) {
                abort(403, 'You can only edit PPP Profiles you created.');
            }
        }

        $routers = ($user->role && $user->role->name === 'super_admin') ? Router::all() : $user->routers;
        
        return view('ppp-profiles.edit', compact('pppProfile', 'routers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PppProfile $pppProfile)
    {
        $request->validate([
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
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            // Non-super_admin can only update profiles they created
            if ($pppProfile->created_by !== $user->id) {
                abort(403, 'You can only update PPP Profiles you created.');
            }
        }

        // Store old values for comparison
        $oldValues = $pppProfile->toArray();

        // Update the profile in database
        $pppProfile->update([
            'local_address' => $request->local_address,
            'remote_address' => $request->remote_address,
            'dns_server' => $request->dns_server,
            'rate_limit' => $request->rate_limit,
            'session_timeout' => $request->session_timeout,
            'idle_timeout' => $request->idle_timeout,
            'only_one' => $request->boolean('only_one'),
            'comment' => $request->comment,
        ]);

        // If profile is synced to MikroTik, update it there too
        if ($pppProfile->mikrotik_id) {
            $router = $pppProfile->router;
            $connected = $this->connectToMikrotik($router);
            
            if ($connected) {
                try {
                    // Prepare update data
                    $updateData = [
                        'local-address' => $pppProfile->local_address,
                        'remote-address' => $pppProfile->remote_address,
                        'dns-server' => $pppProfile->dns_server,
                        'rate-limit' => $pppProfile->rate_limit,
                        'session-timeout' => $pppProfile->session_timeout,
                        'idle-timeout' => $pppProfile->idle_timeout,
                        'only-one' => $pppProfile->only_one ? 'yes' : 'no',
                        'comment' => $pppProfile->comment,
                    ];
                    
                    // Remove null values
                    $updateData = array_filter($updateData, function($value) {
                        return $value !== null && $value !== '';
                    });
                    
                    // Update the profile in MikroTik
                    $result = $this->mikrotikService->updatePppProfile(
                        $pppProfile->mikrotik_id,
                        $updateData
                    );
                    
                    if (!$result['success']) {
                        // Log the error but don't fail the update
                        Log::warning('Failed to update PPP profile in MikroTik', [
                            'profile_id' => $pppProfile->id,
                            'error' => $result['message']
                        ]);
                        
                        // Return JSON response for AJAX
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'PPP Profile updated in database but failed to sync to MikroTik: ' . $result['message']
                            ]);
                        }
                        
                        return redirect()->route('ppp-profiles.index')
                            ->with('warning', 'PPP Profile updated in database but failed to sync to MikroTik: ' . $result['message']);
                    }
                } catch (Exception $e) {
                    Log::error('Exception updating PPP profile in MikroTik', [
                        'profile_id' => $pppProfile->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Return JSON response for AJAX
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'PPP Profile updated in database but failed to sync to MikroTik.'
                        ]);
                    }
                    
                    return redirect()->route('ppp-profiles.index')
                        ->with('warning', 'PPP Profile updated in database but failed to sync to MikroTik.');
                }
            } else {
                // Return JSON response for AJAX
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'PPP Profile updated in database but could not connect to MikroTik router.'
                    ]);
                }
                
                return redirect()->route('ppp-profiles.index')
                    ->with('warning', 'PPP Profile updated in database but could not connect to MikroTik router.');
            }
        }

        // Return JSON response for AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'PPP Profile updated successfully' . ($pppProfile->mikrotik_id ? ' and synced to MikroTik.' : '.')
            ]);
        }

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile updated successfully' . ($pppProfile->mikrotik_id ? ' and synced to MikroTik.' : '.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            // Non-super_admin can only delete profiles they created
            if ($pppProfile->created_by !== $user->id) {
                return response()->json(['success' => false, 'message' => 'You can only delete PPP Profiles you created.'], 403);
            }
        }

        $deleteOption = $request->input('delete_option', 'both');
        $messages = [];
        $errors = [];

        try {
            // Handle deletion based on selected option
            switch ($deleteOption) {
                case 'app_only':
                    // Delete only from application database
                    $pppProfile->delete();
                    $messages[] = 'PPP Profile berhasil dihapus dari aplikasi.';
                    break;

                case 'mikrotik_only':
                    // Delete only from MikroTik
                    if ($pppProfile->mikrotik_id) {
                        $connected = $this->connectToMikrotik($pppProfile->router);
                        if ($connected) {
                            $result = $this->mikrotikService->deletePppProfile($pppProfile->mikrotik_id);
                            if ($result['success']) {
                                // Clear MikroTik ID but keep profile in database
                                $pppProfile->update([
                                    'mikrotik_id' => null,
                                    'is_synced' => false
                                ]);
                                $messages[] = 'PPP Profile berhasil dihapus dari MikroTik.';
                            } else {
                                $errors[] = 'Gagal menghapus profile dari MikroTik: ' . ($result['message'] ?? 'Unknown error');
                            }
                        } else {
                            $errors[] = 'Gagal terhubung ke router MikroTik.';
                        }
                    } else {
                        $errors[] = 'Profile tidak memiliki ID MikroTik untuk dihapus.';
                    }
                    break;

                case 'both':
                default:
                    // Delete from both application and MikroTik
                    if ($pppProfile->mikrotik_id) {
                        $connected = $this->connectToMikrotik($pppProfile->router);
                        if ($connected) {
                            $result = $this->mikrotikService->deletePppProfile($pppProfile->mikrotik_id);
                            if (!$result['success']) {
                                $errors[] = 'Gagal menghapus profile dari MikroTik: ' . ($result['message'] ?? 'Unknown error');
                            } else {
                                $messages[] = 'PPP Profile berhasil dihapus dari MikroTik.';
                            }
                        } else {
                            $errors[] = 'Gagal terhubung ke router MikroTik untuk menghapus profile.';
                        }
                    }
                    
                    // Delete from database regardless of MikroTik result
                    $pppProfile->delete();
                    $messages[] = 'PPP Profile berhasil dihapus dari aplikasi.';
                    break;
            }

            // Prepare response
            if (empty($errors)) {
                return response()->json([
                    'success' => true,
                    'message' => implode(' ', $messages)
                ]);
            } else {
                // Partial success or failure
                $message = '';
                if (!empty($messages)) {
                    $message .= implode(' ', $messages) . ' ';
                }
                if (!empty($errors)) {
                    $message .= 'Namun terjadi error: ' . implode(' ', $errors);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error deleting PPP Profile', [
                'profile_id' => $pppProfile->id,
                'delete_option' => $deleteOption,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus PPP Profile: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync profile to MikroTik
     */
    public function syncToMikrotik(PppProfile $pppProfile)
    {
        $user = auth()->user();
        
        // Check access permissions
        if (!($user->role && $user->role->name === 'super_admin')) {
            if (!$user->routers->contains($pppProfile->router_id)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
        }

        $connected = $this->connectToMikrotik($pppProfile->router);

        if (!$connected) {
            return response()->json(['success' => false, 'message' => 'Failed to connect to router.']);
        }

        try {
            Log::info('Starting PPP Profile sync to MikroTik', [
                'profile_id' => $pppProfile->id,
                'profile_name' => $pppProfile->name,
                'current_mikrotik_id' => $pppProfile->mikrotik_id
            ]);

            // Use MikroTik service to sync the profile
            $result = $this->mikrotikService->syncPppProfile($pppProfile->toArray());
            
            Log::info('PPP Profile sync result', [
                'profile_id' => $pppProfile->id,
                'sync_result' => $result
            ]);
            
            if ($result['success']) {
                // Always update mikrotik_id if returned from sync
                if (isset($result['id']) && $result['id']) {
                    Log::info('Updating profile with MikroTik ID', [
                        'profile_id' => $pppProfile->id,
                        'old_mikrotik_id' => $pppProfile->mikrotik_id,
                        'new_mikrotik_id' => $result['id']
                    ]);

                    $pppProfile->update(['mikrotik_id' => $result['id']]);
                    
                    // Refresh the model to get updated data
                    $pppProfile->refresh();
                    
                    Log::info('Profile updated successfully', [
                        'profile_id' => $pppProfile->id,
                        'updated_mikrotik_id' => $pppProfile->mikrotik_id
                    ]);
                } else {
                    Log::warning('Sync successful but no MikroTik ID returned', [
                        'profile_id' => $pppProfile->id,
                        'result' => $result
                    ]);
                    
                    // Try to find the profile in MikroTik manually
                    try {
                        $profiles = $this->mikrotikService->getPppProfiles();
                        if ($profiles['success']) {
                            foreach ($profiles['data'] as $profile) {
                                if (isset($profile['name']) && $profile['name'] === $pppProfile->name) {
                                    $mikrotikId = $profile['.id'];
                                    $pppProfile->update(['mikrotik_id' => $mikrotikId]);
                                    $pppProfile->refresh();
                                    
                                    Log::info('Found and updated MikroTik ID manually', [
                                        'profile_id' => $pppProfile->id,
                                        'mikrotik_id' => $mikrotikId
                                    ]);
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to manually search for MikroTik ID', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile synced to MikroTik successfully.',
                    'profile' => $pppProfile // Return updated profile data
                ]);
            } else {
                Log::error('PPP Profile sync failed', [
                    'profile_id' => $pppProfile->id,
                    'error_message' => $result['message'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to sync profile to MikroTik.'
                ]);
            }

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
        if (!($user->role && $user->role->name === 'super_admin')) {
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
        if (!($user->role && $user->role->name === 'super_admin')) {
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
        if (!($user->role && $user->role->name === 'super_admin')) {
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
