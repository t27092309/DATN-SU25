<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Models\Brand;
use App\Models\Product;
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
        // Tìm thương hiệu theo slug, nếu không có thì trả về 404
        $brand = Brand::where('slug', $slug)->firstOrFail();

        // Lấy danh sách sản phẩm thuộc thương hiệu đó, bao gồm các quan hệ (relationships)
        $products = Product::where('brand_id', $brand->id)
            ->whereNull('deleted_at')
            ->with([
                'brand',
                'category',
                'usageProfile',
                'scentProfiles.scentGroup',
                'variants.attributeValues.attribute',
                'images',
            ])
            ->get();

        // Trả về một collection của ProductDetailResource
        return ProductDetailResource::collection($products);
    }
}
