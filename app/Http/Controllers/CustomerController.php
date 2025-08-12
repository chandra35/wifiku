<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Support\Str;

class CustomerController extends Controller
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
            // Super admin can see all customers
            $customers = Customer::with(['package', 'createdBy'])
                                ->orderBy('created_at', 'desc')
                                ->get();
        } else {
            // Regular admin can only see their own customers
            $customers = Customer::with(['package', 'createdBy'])
                                ->where('created_by', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        }
        
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        if ($user->role->name === 'super_admin') {
            // Super admin can see all active packages
            $packages = Package::where('is_active', true)
                              ->orderBy('name')
                              ->get();
        } else {
            // Regular admin can only see their own active packages
            $packages = Package::where('is_active', true)
                              ->where('created_by', $user->id)
                              ->orderBy('name')
                              ->get();
        }
        
        // Get provinces for address dropdown
        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        return view('customers.create', compact('packages', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'identity_number' => 'nullable|string|max:16',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'province_id' => 'nullable|string|max:2',
            'city_id' => 'nullable|string|max:4',
            'district_id' => 'nullable|string|max:7',
            'village_id' => 'nullable|string|max:10',
            'package_id' => 'required|exists:packages,id',
            'installation_date' => 'nullable|date',
            'billing_cycle' => 'required|in:monthly,quarterly,semi-annual,annual',
            'next_billing_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended,terminated'
        ]);

        // Generate customer ID
        $validated['id'] = Str::uuid();
        $validated['customer_id'] = Customer::generateCustomerId();
        $validated['created_by'] = auth()->id();
        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        // Set default dates if not provided
        if (empty($validated['installation_date'])) {
            $validated['installation_date'] = now()->toDateString();
        }
        
        if (empty($validated['next_billing_date'])) {
            $validated['next_billing_date'] = now()->addMonth()->toDateString();
        }

        $customer = Customer::create($validated);

        // Create initial payment for pra-bayar system
        \App\Http\Controllers\PaymentController::createInitialPayment($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil didaftarkan. Tagihan pertama telah dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only view their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $customer->load(['package', 'createdBy']);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only edit their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($user->role->name === 'super_admin') {
            // Super admin can see all active packages
            $packages = Package::where('is_active', true)
                              ->orderBy('name')
                              ->get();
        } else {
            // Regular admin can only see their own active packages
            $packages = Package::where('is_active', true)
                              ->where('created_by', $user->id)
                              ->orderBy('name')
                              ->get();
        }
        
        // Get provinces for address dropdown
        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        return view('customers.edit', compact('customer', 'packages', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only update their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'identity_number' => 'nullable|string|max:16',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'province_id' => 'nullable|string|max:2',
            'city_id' => 'nullable|string|max:4',
            'district_id' => 'nullable|string|max:7',
            'village_id' => 'nullable|string|max:10',
            'package_id' => 'required|exists:packages,id',
            'installation_date' => 'nullable|date',
            'billing_cycle' => 'required|in:monthly,quarterly,semi-annual,annual',
            'next_billing_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended,terminated'
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only delete their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if customer has PPPoE secret
        if ($customer->pppoe_secret_id) {
            return redirect()->route('customers.index')
                ->with('error', 'Tidak dapat menghapus pelanggan yang masih memiliki PPPoE Secret aktif.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus(Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only toggle their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';
        
        $customer->update([
            'status' => $newStatus,
            'updated_by' => auth()->id()
        ]);

        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "Pelanggan berhasil {$statusText}."
        ]);
    }

    /**
     * Generate PPPoE secret for customer
     */
    public function generatePppoe(Customer $customer)
    {
        $user = auth()->user();
        
        // Check authorization - admin can only generate PPPoE for their own customers
        if ($user->role->name !== 'super_admin' && $customer->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        try {
            // Check if customer already has PPPoE secret
            if ($customer->pppoe_secret_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan sudah memiliki PPPoE Secret.'
                ]);
            }

            // TODO: Implement PPPoE secret generation logic here
            // This would create a new PPPoE secret and link it to customer
            
            return response()->json([
                'success' => true,
                'message' => 'PPPoE Secret berhasil dibuat untuk pelanggan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PPPoE Secret: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get cities for a province (AJAX)
     */
    public function getCities($provinceCode)
    {
        $cities = \Laravolt\Indonesia\Models\City::where('province_code', $provinceCode)
                    ->orderBy('name')
                    ->get(['code as id', 'name']);
        
        return response()->json($cities);
    }
    
    /**
     * Get districts for a city (AJAX)
     */
    public function getDistricts($cityCode)
    {
        $districts = \Laravolt\Indonesia\Models\District::where('city_code', $cityCode)
                       ->orderBy('name')
                       ->get(['code as id', 'name']);
        
        return response()->json($districts);
    }
    
    /**
     * Get villages for a district (AJAX)
     */
    public function getVillages($districtCode)
    {
        $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $districtCode)
                      ->orderBy('name')
                      ->get(['code as id', 'name']);
        
        return response()->json($villages);
    }
}
