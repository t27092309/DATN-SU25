<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class ClientBrandController extends Controller
{
    public function index()
    {
        $brands = Brand::whereNull('deleted_at') // Chỉ lấy các thương hiệu chưa bị xóa mềm
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($brand) {
                if ($brand->logo) {
                    $brand->logo = asset('storage/' . $brand->logo);
                }
                return $brand;
            });

        return response()->json($brands, 200);
    }
}