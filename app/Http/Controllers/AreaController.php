<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class AreaController extends Controller
{
    /**
     * Get provinces for dropdown
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
        // Get province first to get its code
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
        // Get city first to get its code
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
        // Get district first to get its code
        $district = District::find($districtId);
        if (!$district) {
            return response()->json([], 404);
        }
        
        $villages = Village::where('district_code', $district->code)->orderBy('name')->get();
        return response()->json($villages);
    }

    /**
     * Search areas by query
     */
    public function searchAreas(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // province, city, district, village, all

        $results = [];

        if ($type === 'province' || $type === 'all') {
            $provinces = Province::where('name', 'LIKE', "%{$query}%")->limit(10)->get();
            foreach ($provinces as $province) {
                $results[] = [
                    'id' => $province->id,
                    'name' => $province->name,
                    'type' => 'province',
                    'full_name' => "Provinsi {$province->name}"
                ];
            }
        }

        if ($type === 'city' || $type === 'all') {
            $cities = City::with('province')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();
            foreach ($cities as $city) {
                $results[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'type' => 'city',
                    'province_id' => $city->province_id,
                    'full_name' => "{$city->name}, {$city->province->name}"
                ];
            }
        }

        if ($type === 'district' || $type === 'all') {
            $districts = District::with(['city.province'])
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();
            foreach ($districts as $district) {
                $results[] = [
                    'id' => $district->id,
                    'name' => $district->name,
                    'type' => 'district',
                    'city_id' => $district->city_id,
                    'full_name' => "{$district->name}, {$district->city->name}, {$district->city->province->name}"
                ];
            }
        }

        if ($type === 'village' || $type === 'all') {
            $villages = Village::with(['district.city.province'])
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();
            foreach ($villages as $village) {
                $results[] = [
                    'id' => $village->id,
                    'name' => $village->name,
                    'type' => 'village',
                    'district_id' => $village->district_id,
                    'full_name' => "{$village->name}, {$village->district->name}, {$village->district->city->name}, {$village->district->city->province->name}"
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Get area hierarchy (province -> city -> district -> village)
     */
    public function getAreaHierarchy($type, $id)
    {
        $result = [];

        switch ($type) {
            case 'province':
                $province = Province::find($id);
                if ($province) {
                    $result = [
                        'province' => $province,
                        'cities' => $province->cities()->orderBy('name')->get()
                    ];
                }
                break;

            case 'city':
                $city = City::with('province')->find($id);
                if ($city) {
                    $result = [
                        'province' => $city->province,
                        'city' => $city,
                        'districts' => $city->districts()->orderBy('name')->get()
                    ];
                }
                break;

            case 'district':
                $district = District::with(['city.province'])->find($id);
                if ($district) {
                    $result = [
                        'province' => $district->city->province,
                        'city' => $district->city,
                        'district' => $district,
                        'villages' => $district->villages()->orderBy('name')->get()
                    ];
                }
                break;

            case 'village':
                $village = Village::with(['district.city.province'])->find($id);
                if ($village) {
                    $result = [
                        'province' => $village->district->city->province,
                        'city' => $village->district->city,
                        'district' => $village->district,
                        'village' => $village
                    ];
                }
                break;
        }

        return response()->json($result);
    }
}
