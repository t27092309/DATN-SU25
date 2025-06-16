<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;

class LocationController extends Controller
{
    //http://localhost:8000/api/provinces
    public function provinces()
    {
        return response()->json(Province::select('code', 'name')->get());
    }

    //http://localhost:8000/api/provinces/01/districts
    public function districts($provinceCode)
    {
        // Kiểm tra xem tỉnh có tồn tại không
        $provinceExists = Province::where('code', $provinceCode)->exists();
        if (!$provinceExists) {
            return response()->json([
                'message' => 'Mã tỉnh không tồn tại.'
            ], 404);
        }

        $districts = District::where('province_code', $provinceCode)
            ->select('code', 'name')
            ->get();

        return response()->json($districts);
    }

    //http://localhost:8000/api/districts/001/wards
    public function wards($districtCode)
    {
        // Kiểm tra xem huyện có tồn tại không
        $districtExists = District::where('code', $districtCode)->exists();
        if (!$districtExists) {
            return response()->json([
                'message' => 'Mã huyện không tồn tại.'
            ], 404);
        }

        $wards = Ward::where('district_code', $districtCode)
            ->select('code', 'name')
            ->get();

        return response()->json($wards);
    }
}
