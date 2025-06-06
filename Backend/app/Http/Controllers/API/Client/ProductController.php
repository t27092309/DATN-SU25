<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
     //Vux
    /**
     * Lấy 10 sản phẩm nhiều lượt xem nhất của 4 danh mục bất kỳ.
     * Hiển thị cả tên và slug danh mục ở đầu mỗi nhóm sản phẩm.
     *
     * @return JsonResponse
     */
    // trang chu
    // GET //  http://localhost:8000/api/most-viewed-products-by-categories
   public function getMostViewedProductsByCategories(): JsonResponse
    {
        // Lấy 4 danh mục bất kỳ từ cơ sở dữ liệu.
        $categories = Category::inRandomOrder()->limit(4)->get();
        $data = []; // Mảng để lưu trữ dữ liệu trả về

        foreach ($categories as $category) {
            // Lấy 10 sản phẩm có nhiều lượt xem nhất thuộc danh mục hiện tại.
            // Eager load 'brand', 'images', và 'variants' để resource có thể sử dụng chúng.
            $products = $category->products()
                                 ->with(['brand', 'images', 'variants']) // <-- Eager load các mối quan hệ
                                 ->orderByDesc('views')
                                 ->limit(10)
                                 ->get();

            $data[] = [
                'category_name' => $category->name,
                'category_slug' => $category->slug,
                // Sử dụng ProductResource::collection để định dạng tập hợp các sản phẩm
                'products' => ProductResource::collection($products),
            ];
        }
        // Trả về dữ liệu dưới dạng JSON response
        return response()->json($data);
    }


    // trang danh muc
    // GET // http://localhost:8000/api/category-page-products
    public function getCategoryPageProducts(Request $request): JsonResponse
{
    // Lấy tất cả danh mục thay vì phân trang
    $categories = Category::all();
    $data = [];

    foreach ($categories as $category) {
        // Lấy 10 sản phẩm có nhiều lượt xem nhất
        $products = $category->products()
            ->with(['brand', 'images', 'variants', 'category'])
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $data[] = [
            'category_name' => $category->name,
            'category_slug' => $category->slug,
            'products' => ProductResource::collection($products),
        ];
    }

    return response()->json([
        'data' => $data
    ]);
}
}
