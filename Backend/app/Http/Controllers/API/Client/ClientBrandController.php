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

    public function showBySlug($slug)
    {
        $brand = Brand::where('slug', $slug)->first();

        if (!$brand) {
            return response()->json(['message' => 'Brand not found.'], 404);
        }

        // Biến đổi đường dẫn logo tương tự như phương thức index
        if ($brand->logo) {
            $brand->logo = asset('storage/' . $brand->logo);
        }

        return response()->json($brand, 200);
    }

    public function getProductsByBrandSlug($slug)
    {
        $brand = Brand::where('slug', $slug)->first();

        if (!$brand) {
            return response()->json([
                'message' => 'Brand not found.'
            ], 404);
        }

        $products = $brand->products()->whereNull('deleted_at')->get();
        return response()->json($products, 200);
    }
}
