<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage; // Cần thiết cho upload/delete ảnh phụ
use App\Models\Category; // Import Category cho validation (nếu cần)
use App\Models\Brand;    // Import Brand cho validation (nếu cần)
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductImageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Dùng để quản lý file
use Illuminate\Support\Facades\DB;       // Dùng cho Transaction
use Illuminate\Support\Facades\Log; // 
use Illuminate\Support\Str;              // Dùng để tạo slug
use Illuminate\Validation\Rule;          // Dùng cho validation Rule

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/admin/products
     */
    public function index()
    {
        // Eager load các quan hệ cần thiết cho trang danh sách (chỉ những cái cần hiển thị)
        $products = Product::with(['category', 'brand']) // Không load images và variants để giữ nhẹ dữ liệu
            ->orderByDesc('id')
            ->paginate(15);

        // TRẢ VỀ RESOURCE COLLECTION CHO DANH SÁCH
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/admin/products
     */
    public function store(Request $request)
    {
        // 1. Validation cơ bản cho sản phẩm
        $rules = [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Ảnh chính của sản phẩm
            'has_variants' => 'required|boolean', // Rule mới
        ];

        // 2. Thêm rules tùy thuộc vào has_variants
        if ($request->boolean('has_variants')) {
            // Rules cho sản phẩm có biến thể
            $rules['variants'] = 'required|array|min:1'; // Phải có ít nhất 1 biến thể
            $rules['variants.*.sku'] = 'required|string|max:255|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'; // Ảnh biến thể
            $rules['variants.*.attribute_values'] = 'required|array|min:1'; // Mỗi biến thể phải có ít nhất 1 thuộc tính
            $rules['variants.*.attribute_values.*'] = 'exists:attribute_values,id'; // Giá trị thuộc tính phải tồn tại
        } else {
            // Rules cho sản phẩm không có biến thể
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
        }

        $validatedData = $request->validate($rules);

        // Bắt đầu Transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            // 3. Xử lý tải lên ảnh chính (nếu có)
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // 4. Tạo sản phẩm
            // Slug sẽ tự động được tạo bởi mutator setNameAttribute trong Product Model
            $productData = [
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'gender' => $validatedData['gender'],
                'category_id' => $validatedData['category_id'],
                'brand_id' => $validatedData['brand_id'],
                'image' => $imagePath,
                'has_variants' => $validatedData['has_variants'],
            ];

            // Thêm giá và tồn kho cho sản phẩm đơn giản nếu không có biến thể
            if (!$validatedData['has_variants']) {
                $productData['price'] = $validatedData['price'];
                $productData['stock'] = $validatedData['stock'];
            }

            $product = Product::create($productData);

            // 5. Xử lý biến thể (nếu có)
            if ($validatedData['has_variants']) {
                foreach ($validatedData['variants'] as $variantData) {
                    $variantImagePath = null;
                    // Lấy file ảnh cho biến thể cụ thể
                    if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantImagePath = $variantData['image']->store('product_variants', 'public');
                    }

                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'image' => $variantImagePath,
                    ]);

                    // Đồng bộ các giá trị thuộc tính cho biến thể
                    $variant->attributeValues()->sync($variantData['attribute_values']);
                }
            }

            DB::commit(); // Hoàn tất transaction
            return response()->json([
                'message' => 'Sản phẩm và biến thể (nếu có) đã được thêm thành công!',
                'data' => new ProductDetailResource($product) // Trả về resource chi tiết
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi
            if ($imagePath && Storage::disk('public')->exists($imagePath)) { // Chỉ xóa nếu ảnh đã được upload và tồn tại
                Storage::disk('public')->delete($imagePath);
            }            // Log lỗi chi tiết hơn cho việc debug
            Log::error("Lỗi khi thêm sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['message' => 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/admin/products/{product}
     */
    public function show(Product $product)
    {
        // Eager load tất cả các quan hệ cần thiết cho trang chi tiết
        $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute']); // Load biến thể và các giá trị thuộc tính của biến thể
        // TRẢ VỀ PRODUCT DETAIL RESOURCE
        return new ProductDetailResource($product);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/admin/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string',
            'gender' => 'sometimes|in:male,female,unisex',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Ảnh chính mới
            'remove_main_image' => 'boolean', // Cờ để xóa ảnh chính hiện tại

            // Validation for variants
            'variants' => 'nullable|json', // Expect variants as a JSON string
            // You can add more specific rules for variants array if needed after decoding
            // For example, 'variants.*.sku' => 'required|string|max:255|unique:product_variants,sku,' . $variant->id
            // This kind of nested validation is easier after json_decode.
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'price.numeric' => 'Giá phải là một số.',
            'price.min' => 'Giá không được nhỏ hơn 0.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
            'variants.json' => 'Dữ liệu biến thể không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            $currentImagePath = $product->image;

            // Xử lý xóa ảnh chính hiện tại
            if (isset($validated['remove_main_image']) && $validated['remove_main_image'] && $currentImagePath) {
                if (Storage::disk('public')->exists($currentImagePath)) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $validated['image'] = null; // Set ảnh về null trong DB
            }

            // Xử lý upload ảnh chính mới
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có ảnh mới và ảnh cũ tồn tại
                if ($currentImagePath && Storage::disk('public')->exists($currentImagePath)) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            } else if (!isset($validated['image']) && !isset($validated['remove_main_image'])) {
                // Nếu không có ảnh mới và không yêu cầu xóa, giữ ảnh cũ
                $validated['image'] = $currentImagePath;
            }

            // Cập nhật slug nếu tên thay đổi
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Update main product details
            $product->update($validated);

            // --- Handle Variants ---
            $submittedVariantsData = [];
            if (isset($validated['variants'])) {
                $submittedVariantsData = json_decode($validated['variants'], true); // Decode the JSON string
                // Basic check if decoding was successful and it's an array
                if (!is_array($submittedVariantsData)) {
                    throw new \Exception('Dữ liệu biến thể không phải là một mảng hợp lệ.');
                }
            }

            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $variantsToKeepIds = [];

            foreach ($submittedVariantsData as $variantData) {
                // Determine if it's an existing variant or a new one
                if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                    // This is an existing variant, update it
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant) {
                        // Validate variant specific fields
                        $variantValidated = validator($variantData, [
                            'sku' => ['required', 'string', 'max:255', Rule::unique('product_variants')->ignore($variant->id)],
                            'price' => 'required|numeric|min:0',
                            'stock' => 'required|integer|min:0',
                            'sold' => 'sometimes|integer|min:0',
                            'status' => 'sometimes|string',
                            'barcode' => 'nullable|string|max:255',
                            'description' => 'nullable|string',
                        ])->validate();

                        $variant->update($variantValidated);
                        $variantsToKeepIds[] = $variant->id;

                        // Sync attribute values for the updated variant
                        if (isset($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])) {
                            // Only attach values that exist in AttributeValue model
                            $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_value_ids'])->pluck('id');
                            $variant->attributeValues()->sync($validAttributeValueIds);
                        } else {
                            $variant->attributeValues()->detach(); // Remove all if none submitted
                        }
                    }
                } else {
                    // This is a new variant, create it
                    $newVariantData = validator($variantData, [
                        'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
                        'price' => 'required|numeric|min:0',
                        'stock' => 'required|integer|min:0',
                        'sold' => 'sometimes|integer|min:0',
                        'status' => 'sometimes|string',
                        'barcode' => 'nullable|string|max:255',
                        'description' => 'nullable|string',
                    ])->validate();

                    $newVariant = $product->variants()->create($newVariantData);
                    $variantsToKeepIds[] = $newVariant->id;

                    // Attach attribute values for the new variant
                    if (isset($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])) {
                        $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_value_ids'])->pluck('id');
                        $newVariant->attributeValues()->attach($validAttributeValueIds);
                    }
                }
            }

            // Delete variants that were removed from the frontend
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $variantsToKeepIds)
                ->delete(); // This will also soft delete due to SoftDeletes trait

            DB::commit();

            // Load the necessary relations to return ProductDetailResource
            $product->load([
                'images',
                'category',
                'brand',
                // Eager load variants with their attribute values and attributes
                'variants.attributeValues.attribute'
            ]);
            return new ProductDetailResource($product);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi validation khi cập nhật sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật sản phẩm.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/admin/products/{product}
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            // Xóa ảnh chính
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Xóa tất cả ảnh phụ và file của chúng
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
                $image->delete(); // Xóa bản ghi ProductImage
            }

            // Xóa tất cả các biến thể liên quan và các quan hệ của chúng (ProductVariantController sẽ xử lý xóa mềm)
            // Nếu bạn muốn xóa cứng ProductVariant khi xóa Product, có thể dùng $product->variants()->forceDelete();
            $product->variants()->delete(); // Thực hiện soft delete cho tất cả biến thể liên quan

            $product->delete(); // Xóa sản phẩm

            DB::commit();
            return response()->noContent(); // Trả về 204 No Content cho xóa thành công

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa sản phẩm.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload / Cập nhật ảnh chính cho sản phẩm.
     * POST /api/admin/products/{product}/image
     */
    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh chính.',
            // ... (thêm thông báo lỗi chi tiết khác nếu cần)
        ]);

        DB::beginTransaction();
        try {
            // Xóa ảnh cũ nếu có
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Lưu ảnh mới
            $path = $request->file('image')->store('products', 'public');
            $product->update(['image' => $path]);

            DB::commit();
            return response()->json([
                'message' => 'Ảnh chính đã được cập nhật thành công.',
                'image_url' => Storage::url($path), // Trả về URL ảnh mới
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật ảnh chính.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload / Cập nhật ảnh phụ cho sản phẩm.
     * POST /api/admin/products/{product}/images
     * (Phương thức này sẽ xóa tất cả ảnh phụ cũ và thêm ảnh mới)
     */
    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'images.*.required' => 'Vui lòng chọn ảnh phụ.',
            // ... (thêm thông báo lỗi chi tiết khác nếu cần)
        ]);

        DB::beginTransaction();
        try {
            // Xóa tất cả ảnh phụ cũ và file của chúng
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
                $image->delete(); // Xóa bản ghi ProductImage
            }

            // Lưu ảnh phụ mới
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create(['image_url' => $path]);
            }

            DB::commit();
            $product->load('images'); // Load lại ảnh phụ để trả về
            return response()->json([
                'message' => 'Ảnh phụ đã được cập nhật thành công.',
                'data' => ProductImageResource::collection($product->images), // Trả về Resource Collection
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật ảnh phụ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa một ảnh phụ cụ thể của sản phẩm.
     * DELETE /api/admin/products/images/{imageId}
     */
    public function deleteImage(string $imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        DB::beginTransaction();
        try {
            // Xóa file ảnh khỏi storage
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }

            // Xóa bản ghi DB
            $image->delete();

            DB::commit();
            return response()->json(['message' => 'Ảnh phụ đã được xóa thành công.', 'image_id' => $imageId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa ảnh phụ.', 'error' => $e->getMessage()], 500);
        }
    }
}
