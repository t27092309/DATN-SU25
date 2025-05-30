<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::with(['images', 'variants'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product = Product::create($validated);

        return response()->json($product, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Product::with(['images', 'variants'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:products,slug,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'gender' => 'in:male,female,unisex',
            'price' => 'nullable|numeric',
            'category_id' => 'exists:categories,id',
            'brand_id' => 'exists:brands,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $product->id
        ], 200);
    }

    //Vux
    /**
     * Lấy 10 sản phẩm nhiều lượt xem nhất của 4 danh mục bất kỳ.
     * Hiển thị cả tên và slug danh mục ở đầu mỗi nhóm sản phẩm.
     *
     * @return JsonResponse
     */
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
    }

