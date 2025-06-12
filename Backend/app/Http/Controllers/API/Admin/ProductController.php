<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ProductImage;
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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'images.*.image' => 'Mỗi ảnh phụ phải là hình ảnh.',
            'images.*.mimes' => 'Mỗi ảnh phụ phải có định dạng jpeg, png, jpg, gif.',
            'images.*.max' => 'Mỗi ảnh phụ không được vượt quá 2MB.',
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

        // Lưu ảnh phụ nếu có
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['image_url' => $path]);
            }
        }

        return response()->json($product->load('images'), 201);
    }

    // GET // http://localhost:8000/api/products/{id}


    public function show(string $id)
    {
        return Product::with(['images', 'variants'])->findOrFail($id);
    }

    // PUT // http://localhost:8000/api/products/{id}

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'gender' => 'sometimes|in:male,female,unisex',
            'price' => 'nullable|numeric',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
        ], [
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'slug.string' => 'Slug phải là chuỗi ký tự.',
            'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'gender.in' => 'Giới tính phải là một trong các giá trị: male, female, unisex.',
            'price.numeric' => 'Giá phải là một số.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load('images'),
            // 'all' => $request->all(),
            // 'validated' => $validated,
        ], 200);
    }

    // update ảnh chính
    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh chính.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $path = $request->file('image')->store('products', 'public');
        $product->update(['image' => $path]);

        return response()->json([
            'message' => 'Ảnh chính đã được cập nhật thành công.',
            'data' => $product,
        ]);
    }


    //update ảnh phụ
    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'images.*.required' => 'Vui lòng chọn ảnh phụ.',
            'images.*.image' => 'Mỗi tệp ảnh phụ phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, gif.',
            'images.*.max' => 'Mỗi ảnh phụ không được vượt quá 2MB.',
        ]);

        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
            $image->delete();
        }

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create(['image_url' => $path]);
        }

        return response()->json([
            'message' => 'Ảnh phụ đã được cập nhật thành công.',
            'data' => $product->load('images'),
        ], 200);
    }
    // DELETE // http://localhost:8000/api/products/{id}

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }


        // Xoá toàn bộ ảnh phụ liên quan
        foreach ($product->images as $image) {
            Storage::delete('public/' . $image->image_url); // Xoá file thật
            $image->delete(); // Xoá bản ghi DB
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $product->id
        ], 200);
    }

    public function deleteImage(string $imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        // Xoá file ảnh khỏi storage
        Storage::disk('public')->delete($image->image_url);

        // Xoá bản ghi DB
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully.',
            'image_id' => $imageId
        ], 200);
    }
}
