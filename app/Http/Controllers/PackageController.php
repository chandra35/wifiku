<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Router;
use App\Models\PppProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role->name === 'super_admin') {
            // Super admin can see all packages
            $packages = Package::with(['router', 'createdBy', 'pppProfile', 'customers'])
                              ->orderBy('sort_order')
                              ->orderBy('name')
                              ->get();
        } else {
            // Regular admin can only see their own packages
            $packages = Package::with(['router', 'createdBy', 'pppProfile'])
                              ->where('created_by', $user->id)
                              ->orderBy('sort_order')
                              ->orderBy('name')
                              ->get();
        }
        
        return view('packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Get routers based on user role
        if ($user->role && $user->role->name === 'super_admin') {
            $routers = Router::orderBy('name')->get();
        } else {
            // Get routers assigned to this user using collection
            $routers = $user->routers->sortBy('name');
        }
        
        return view('packages.create', compact('routers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'router_id' => 'required|exists:routers,id',
            'ppp_profile_id' => 'required|exists:ppp_profiles,id',
            'rate_limit' => 'nullable|string|max:255', // Will be auto-filled from PPP Profile
            'price' => 'required|numeric|min:0',
            'price_before_tax' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi-annual,annual',
            'is_active' => 'boolean'
        ]);

        // Parse price values using helper function
        $validated['price'] = $this->parsePrice($validated['price']);
        
        // Calculate price before tax if not provided or incorrect
        if (empty($validated['price_before_tax'])) {
            $validated['price_before_tax'] = round($validated['price'] / 1.11);
        } else {
            $validated['price_before_tax'] = $this->parsePrice($validated['price_before_tax']);
            // Verify calculation is correct
            $calculatedPriceBeforeTax = round($validated['price'] / 1.11);
            if (abs($validated['price_before_tax'] - $calculatedPriceBeforeTax) > 1) {
                $validated['price_before_tax'] = $calculatedPriceBeforeTax;
            }
        }

        // Get rate_limit from PPP Profile to ensure consistency
        $pppProfile = \App\Models\PppProfile::find($validated['ppp_profile_id']);
        if ($pppProfile) {
            $validated['rate_limit'] = $pppProfile->rate_limit;
        }

        $validated['id'] = Str::uuid();
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->has('is_active');

        Package::create($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        $user = auth()->user();
        
        // Check if user can view this package (super_admin or own package)
        if ($user->role->name !== 'super_admin' && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $package->load(['router', 'createdBy', 'customers']);
        return view('packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        $user = auth()->user();
        
        // Check if user can edit this package (super_admin or own package)
        if ($user->role->name !== 'super_admin' && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get routers based on user role
        if ($user->role->name === 'super_admin') {
            $routers = Router::orderBy('name')->get();
        } else {
            // Get routers assigned to this user using collection
            $routers = $user->routers->sortBy('name');
        }
        
        // Get PPP Profiles for the current router, filtered by user
        if ($user->role->name === 'super_admin') {
            $pppProfiles = PppProfile::where('router_id', $package->router_id)
                                    ->orderBy('name')
                                    ->get();
        } else {
            $pppProfiles = PppProfile::where('router_id', $package->router_id)
                                    ->where('created_by', $user->id)
                                    ->orderBy('name')
                                    ->get();
        }
        
        return view('packages.edit', compact('package', 'routers', 'pppProfiles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $user = auth()->user();
        
        // Check if user can update this package (super_admin or own package)
        if ($user->role->name !== 'super_admin' && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'router_id' => 'required|exists:routers,id',
            'ppp_profile_id' => 'required|exists:ppp_profiles,id',
            'rate_limit' => 'nullable|string|max:255', // Will be auto-filled from PPP Profile
            'price' => 'required|numeric|min:0',
            'price_before_tax' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi-annual,annual',
            'is_active' => 'boolean'
        ]);

        // Parse price values using helper function
        $validated['price'] = $this->parsePrice($validated['price']);
        
        // Calculate price before tax if not provided or incorrect
        if (empty($validated['price_before_tax'])) {
            $validated['price_before_tax'] = round($validated['price'] / 1.11);
        } else {
            $validated['price_before_tax'] = $this->parsePrice($validated['price_before_tax']);
            // Verify calculation is correct
            $calculatedPriceBeforeTax = round($validated['price'] / 1.11);
            if (abs($validated['price_before_tax'] - $calculatedPriceBeforeTax) > 1) {
                $validated['price_before_tax'] = $calculatedPriceBeforeTax;
            }
        }

        // Get rate_limit from PPP Profile to ensure consistency
        $pppProfile = \App\Models\PppProfile::find($validated['ppp_profile_id']);
        if ($pppProfile) {
            $validated['rate_limit'] = $pppProfile->rate_limit;
        }

        $validated['updated_by'] = auth()->id();
        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        $user = auth()->user();
        
        // Check if user can delete this package (super_admin or own package)
        if ($user->role->name !== 'super_admin' && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if package has customers
        if ($package->customers()->count() > 0) {
            return redirect()->route('packages.index')
                ->with('error', 'Tidak dapat menghapus paket yang masih memiliki pelanggan.');
        }

        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * Get PPP Profiles by Router ID
     */
    public function getPppProfiles(Request $request)
    {
        try {
            $routerId = $request->get('router_id');
            
            if (!$routerId) {
                return response()->json(['error' => 'Router ID is required'], 400);
            }
            
            // Check if router exists
            $router = Router::find($routerId);
            if (!$router) {
                return response()->json(['error' => 'Router not found'], 404);
            }
            
            $user = auth()->user();
            
            // Verify user has access to this router (only for non-super_admin)
            if ($user->role && $user->role->name !== 'super_admin') {
                $hasAccess = $user->routers->contains('id', $routerId);
                if (!$hasAccess) {
                    return response()->json(['error' => 'Access denied to this router'], 403);
                }
            }
            
            // Get PPP Profiles for this router with role-based filtering
            $query = PppProfile::where('router_id', $routerId);
            
            // Apply role-based filtering for non-super_admin users
            if ($user->role && $user->role->name !== 'super_admin') {
                // Non-super_admin can only see PPP Profiles they created themselves
                $query->where('created_by', $user->id);
            }
            
            $pppProfiles = $query->orderBy('name')->get();
            
            // Log for debugging
            Log::info("getPppProfiles - Router ID: {$routerId}, User: {$user->name} ({$user->role->name}), Found profiles: " . $pppProfiles->count());
            
            if ($pppProfiles->isEmpty()) {
                return response()->json([]);
            }
            
            $profilesData = $pppProfiles->map(function($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'display_name' => $profile->name . ' - ' . ($profile->rate_limit ?: 'No Rate Limit'),
                    'rate_limit' => $profile->rate_limit ?: '',
                    'burst_limit' => $profile->burst_limit ?: '',
                    'burst_threshold' => $profile->burst_threshold ?: '',
                    'burst_time' => $profile->burst_time ?: '',
                ];
            });
            
            return response()->json($profilesData);
            
        } catch (\Exception $e) {
            Log::error('Error in getPppProfiles: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle package status
     */
    public function toggleStatus(Package $package)
    {
        $package->update([
            'is_active' => !$package->is_active,
            'updated_by' => auth()->id()
        ]);

        $status = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "Paket berhasil {$status}."
        ]);
    }

    /**
     * Sync package to MikroTik as PPP Profile
     */
    public function syncToMikrotik(Package $package)
    {
        try {
            // TODO: Implement MikroTik sync logic here
            // This would create/update PPP Profile in MikroTik
            
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil disinkronisasi ke MikroTik sebagai PPP Profile.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi ke MikroTik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse price value to handle Indonesian number format
     */
    private function parsePrice($price)
    {
        // Convert to string first to handle proper parsing
        $price = (string) $price;
        $price = trim($price);
        
        // If it's a simple number without any formatting
        if (preg_match('/^\d+$/', $price)) {
            return (float) $price;
        }
        
        // Check if it's Indonesian format (dots as thousand separator)
        // Pattern: 200.000 or 200.000.000 (no decimal places or with comma decimal)
        if (preg_match('/^\d{1,3}(\.\d{3})*$/', $price)) {
            // Indonesian format: dots are thousand separators
            $cleaned = str_replace('.', '', $price); // Remove thousand separators
            return (float) $cleaned;
        }
        
        if (preg_match('/^\d{1,3}(\.\d{3})*,\d{1,2}$/', $price)) {
            // Indonesian format with decimal: 200.000,50
            $cleaned = str_replace('.', '', $price); // Remove thousand separators
            $cleaned = str_replace(',', '.', $cleaned); // Convert comma decimal to dot
            return (float) $cleaned;
        }
        
        // Check if it's international format (commas as thousand separator)
        // Pattern: 200,000 or 200,000.00
        if (preg_match('/^\d{1,3}(,\d{3})*(\.\d{1,2})?$/', $price)) {
            // International format: commas are thousand separators
            $cleaned = str_replace(',', '', $price); // Remove thousand separators
            return (float) $cleaned;
        }
        
        // Fallback: remove all non-numeric except last dot/comma
        $cleaned = preg_replace('/[^\d.,]/', '', $price);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
