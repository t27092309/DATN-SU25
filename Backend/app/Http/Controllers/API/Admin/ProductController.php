<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    // GET // http://localhost:8000/api/products

    public function index()
    {
        $products = Product::with(['images', 'variants'])
            ->orderByDesc('id')  // hoặc ->latest('id')
            ->paginate(15);

        return response()->json($products);
    }

    // POST // http://localhost:8000/api/products


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
        ], [
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'slug.required' => 'Slug là bắt buộc.',
            'slug.string' => 'Slug phải là chuỗi ký tự.',
            'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'gender.required' => 'Giới tính là bắt buộc.',
            'gender.in' => 'Giới tính phải là một trong các giá trị: male, female, unisex.',
            'price.numeric' => 'Giá phải là một số.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    


    public function show($id)
{
//     $product = Product::with(['usageProfile', 'scentProfiles', 'variants', 'images'])->findOrFail($id);
// return new ProductDetailResource($product);

}
    // GET // http://localhost:8000/api/products/{id}

    // PUT // http://localhost:8000/api/products/{id}

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
        ], [
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'slug.string' => 'Slug phải là chuỗi ký tự.',
            'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'gender.in' => 'Giới tính phải là một trong các giá trị: male, female, unisex.',
            'price.numeric' => 'Giá phải là một số.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        return response()->json($product);
    }

    // DELETE // http://localhost:8000/api/products/{id}

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $product->id
        ], 200);
    }
}
