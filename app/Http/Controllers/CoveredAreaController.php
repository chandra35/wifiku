<?php

namespace App\Http\Controllers;

use App\Models\CoveredArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Yajra\DataTables\Facades\DataTables;

class CoveredAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = CoveredArea::with(['user', 'province', 'city', 'district', 'village']);
        
        // Jika Super Admin, bisa melihat semua covered areas
        // Jika Admin/POP, hanya bisa melihat covered areas mereka sendiri
        if ($user->role !== 'Super Admin') {
            $query->where('user_id', $user->id);
        }
        
        // If AJAX request for DataTable
        if ($request->ajax()) {
            $coveredAreas = $query->latest();
            
            return DataTables::of($coveredAreas)
                ->addIndexColumn()
                ->addColumn('area_info', function($row) {
                    return $row->complete_area;
                })
                ->addColumn('status_badge', function($row) {
                    return $row->status_badge;
                })
                ->addColumn('action', function($row) {
                    $buttons = '';
                    
                    // Edit button
                    $buttons .= '<button type="button" class="btn btn-sm btn-warning btn-edit mr-1" 
                                    data-id="'.$row->id.'" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    
                    // Toggle status button
                    $statusIcon = $row->status === 'active' ? 'fa-eye-slash' : 'fa-eye';
                    $statusTitle = $row->status === 'active' ? 'Nonaktifkan' : 'Aktifkan';
                    $buttons .= '<button type="button" class="btn btn-sm btn-info btn-toggle-status mr-1" 
                                    data-id="'.$row->id.'" data-status="'.$row->status.'" 
                                    title="'.$statusTitle.'">
                                    <i class="fas '.$statusIcon.'"></i>
                                </button>';
                    
                    // Delete button
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" 
                                    data-id="'.$row->id.'" data-area="'.$row->complete_area.'" 
                                    title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    
                    return '<div class="btn-group">'.$buttons.'</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        
        $coveredAreas = $query->latest()->get();
        
        return view('covered-areas.index', compact('coveredAreas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::orderBy('name')->get();
        return view('covered-areas.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'province_id' => 'required|exists:indonesia_provinces,id',
            'city_id' => 'required|exists:indonesia_cities,id',
            'district_id' => 'required|exists:indonesia_districts,id',
            'village_id' => 'nullable|exists:indonesia_villages,id',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $userId = Auth::id();
            
            // Cek apakah area sudah ada untuk user ini
            $query = CoveredArea::where('user_id', $userId)
                ->where('province_id', $request->province_id)
                ->where('city_id', $request->city_id)
                ->where('district_id', $request->district_id);
                
            // Jika village_id diisi, cek duplikasi dengan village_id yang sama
            if ($request->village_id) {
                $query->where('village_id', $request->village_id);
                $exists = $query->exists();
                
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Area coverage sudah ada untuk desa/kelurahan ini!'
                    ], 422);
                }
            } else {
                // Jika village_id kosong (coverage untuk seluruh kecamatan)
                // Cek apakah sudah ada coverage untuk kecamatan ini (null village_id)
                $existsDistrictWide = $query->whereNull('village_id')->exists();
                
                if ($existsDistrictWide) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Coverage untuk seluruh kecamatan ini sudah ada!'
                    ], 422);
                }
                
                // Cek apakah ada coverage spesifik desa di kecamatan ini
                $existsSpecificVillages = $query->whereNotNull('village_id')->exists();
                
                if ($existsSpecificVillages) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sudah ada coverage spesifik desa di kecamatan ini. Hapus coverage desa tersebut terlebih dahulu jika ingin menggunakan coverage kecamatan.'
                    ], 422);
                }
            }

            CoveredArea::create([
                'user_id' => $userId,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'village_id' => $request->village_id,
                'description' => $request->description,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Area coverage berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CoveredArea $coveredArea)
    {
        // Pastikan user hanya bisa melihat covered area mereka sendiri (kecuali Super Admin)
        if (Auth::user()->role !== 'Super Admin' && $coveredArea->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $coveredArea->load(['user', 'province', 'city', 'district', 'village']);
        return view('covered-areas.show', compact('coveredArea'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoveredArea $coveredArea)
    {
        // Pastikan user hanya bisa edit covered area mereka sendiri (kecuali Super Admin)
        if (Auth::user()->role !== 'Super Admin' && $coveredArea->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $coveredArea->load(['user', 'province', 'city', 'district', 'village']);
        
        // If AJAX request, return JSON
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'coveredArea' => $coveredArea
            ]);
        }
        
        // Otherwise return view for traditional form
        $provinces = Province::orderBy('name')->get();
        
        $cities = collect();
        $districts = collect();
        $villages = collect();
        
        if ($coveredArea->province_id) {
            $province = Province::find($coveredArea->province_id);
            if ($province) {
                $cities = City::where('province_code', $province->code)->orderBy('name')->get();
            }
        }
        
        if ($coveredArea->city_id) {
            $city = City::find($coveredArea->city_id);
            if ($city) {
                $districts = District::where('city_code', $city->code)->orderBy('name')->get();
            }
        }
        
        if ($coveredArea->district_id) {
            $district = District::find($coveredArea->district_id);
            if ($district) {
                $villages = Village::where('district_code', $district->code)->orderBy('name')->get();
            }
        }
        
        return view('covered-areas.edit', compact('coveredArea', 'provinces', 'cities', 'districts', 'villages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoveredArea $coveredArea)
    {
        // Pastikan user hanya bisa update covered area mereka sendiri (kecuali Super Admin)
        if (Auth::user()->role !== 'Super Admin' && $coveredArea->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'province_id' => 'required|exists:indonesia_provinces,id',
            'city_id' => 'required|exists:indonesia_cities,id',
            'district_id' => 'required|exists:indonesia_districts,id',
            'village_id' => 'nullable|exists:indonesia_villages,id',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            // Cek apakah area sudah ada untuk user ini (kecuali record yang sedang diupdate)
            $query = CoveredArea::where('user_id', $coveredArea->user_id)
                ->where('province_id', $request->province_id)
                ->where('city_id', $request->city_id)
                ->where('district_id', $request->district_id)
                ->where('id', '!=', $coveredArea->id);
                
            // Jika village_id diisi, cek duplikasi dengan village_id yang sama
            if ($request->village_id) {
                $query->where('village_id', $request->village_id);
                $exists = $query->exists();
                
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Area coverage sudah ada untuk desa/kelurahan ini!'
                    ], 422);
                }
            } else {
                // Jika village_id kosong (coverage untuk seluruh kecamatan)
                // Cek apakah sudah ada coverage untuk kecamatan ini (null village_id)
                $existsDistrictWide = $query->whereNull('village_id')->exists();
                
                if ($existsDistrictWide) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Coverage untuk seluruh kecamatan ini sudah ada!'
                    ], 422);
                }
                
                // Cek apakah ada coverage spesifik desa di kecamatan ini
                $existsSpecificVillages = $query->whereNotNull('village_id')->exists();
                
                if ($existsSpecificVillages) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sudah ada coverage spesifik desa di kecamatan ini. Hapus coverage desa tersebut terlebih dahulu jika ingin menggunakan coverage kecamatan.'
                    ], 422);
                }
            }

            $coveredArea->update([
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'village_id' => $request->village_id,
                'description' => $request->description,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Area coverage berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoveredArea $coveredArea)
    {
        // Pastikan user hanya bisa hapus covered area mereka sendiri (kecuali Super Admin)
        if (Auth::user()->role !== 'Super Admin' && $coveredArea->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $coveredArea->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Area coverage berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $provinces = Province::orderBy('name')->get();
        return response()->json($provinces);
    }

    /**
     * Get cities by province
     */
    public function getCities($provinceId)
    {
        // Ambil province code berdasarkan province ID
        $province = Province::find($provinceId);
        if (!$province) {
            return response()->json([], 404);
        }
        
        $cities = City::where('province_code', $province->code)->orderBy('name')->get();
        return response()->json($cities);
    }

    /**
     * Get districts by city
     */
    public function getDistricts($cityId)
    {
        // Ambil city code berdasarkan city ID
        $city = City::find($cityId);
        if (!$city) {
            return response()->json([], 404);
        }
        
        $districts = District::where('city_code', $city->code)->orderBy('name')->get();
        return response()->json($districts);
    }

    /**
     * Get villages by district
     */
    public function getVillages($districtId)
    {
        // Ambil district code berdasarkan district ID
        $district = District::find($districtId);
        if (!$district) {
            return response()->json([], 404);
        }
        
        $villages = Village::where('district_code', $district->code)->orderBy('name')->get();
        return response()->json($villages);
    }

    /**
     * Toggle status of covered area
     */
    public function toggleStatus(CoveredArea $coveredArea)
    {
        // Pastikan user hanya bisa toggle status covered area mereka sendiri (kecuali Super Admin)
        if (Auth::user()->role !== 'Super Admin' && $coveredArea->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $newStatus = $coveredArea->status === 'active' ? 'inactive' : 'active';
            $coveredArea->update(['status' => $newStatus]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status area coverage berhasil diubah!',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
