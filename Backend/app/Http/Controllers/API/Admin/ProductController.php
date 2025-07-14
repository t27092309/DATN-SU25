<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage; // Essential for handling additional images
use App\Models\Category;
use App\Models\Brand;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductImageResource; // Still useful if you want to return individual image details
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException; // Import for specific exception handling

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/admin/products
     */
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->orderByDesc('id')
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Main product image
            'gallery_images' => 'nullable|array', // Allows an array of files
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4096', // Each file in the array
            'has_variants' => 'required|boolean',
            'scent_groups' => 'nullable|array', // New: Validation for scent groups
            'scent_groups.*.id' => 'required|exists:scent_groups,id',
            'scent_groups.*.strength' => 'required|integer|min:1|max:5',
        ];

        // Add validation rules depending on product type (with/without variants)
        if ($request->boolean('has_variants')) {
            $rules['variants'] = 'required|array|min:1';
            // Validate that attribute values for variants are unique within a product
            // This is a complex rule that might need a custom validation if you want to ensure
            // combinations like (Red, Small) are unique across all variants of THIS product.
            // For now, unique SKU is sufficient.
            $rules['variants.*.sku'] = 'required|string|max:255|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['variants.*.attribute_values'] = 'required|array|min:1';
            $rules['variants.*.attribute_values.*'] = 'exists:attribute_values,id';
        } else {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
            // Add a rule for SKU of the default variant if needed
            // For a default variant, you might want to generate SKU automatically,
            // so no need to validate from request for this case.
        }

        try {
            // Perform Validation
            $validatedData = $request->validate($rules, [
                'name.required' => 'Tên sản phẩm là bắt buộc.',
                'name.unique' => 'Tên sản phẩm này đã tồn tại.',
                'description.string' => 'Mô tả phải là văn bản.',
                'gender.required' => 'Giới tính là bắt buộc.',
                'gender.in' => 'Giới tính không hợp lệ.',
                'category_id.required' => 'Danh mục là bắt buộc.',
                'category_id.exists' => 'Danh mục không hợp lệ.',
                'brand_id.required' => 'Thương hiệu là bắt buộc.',
                'brand_id.exists' => 'Thương hiệu không hợp lệ.',
                'image.image' => 'Tệp ảnh chính phải là hình ảnh.',
                'image.mimes' => 'Định dạng ảnh chính không hợp lệ. Chỉ chấp nhận: jpeg, png, jpg, gif, svg.',
                'image.max' => 'Kích thước ảnh chính không được vượt quá 2MB.', // Corrected from 4MB as per rule
                'gallery_images.array' => 'Thư viện ảnh phải là một mảng.',
                'gallery_images.*.image' => 'Mỗi tệp trong thư viện ảnh phải là một hình ảnh.',
                'gallery_images.*.mimes' => 'Mỗi tệp trong thư viện ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
                'gallery_images.*.max' => 'Mỗi tệp trong thư viện ảnh không được lớn hơn 4MB.', // Corrected from 2MB as per rule
                'has_variants.required' => 'Loại sản phẩm là bắt buộc.',
                'has_variants.boolean' => 'Loại sản phẩm không hợp lệ.',
                'price.required' => 'Giá sản phẩm là bắt buộc.',
                'price.numeric' => 'Giá sản phẩm phải là số.',
                'price.min' => 'Giá sản phẩm không được âm.',
                'stock.required' => 'Tồn kho là bắt buộc.',
                'stock.integer' => 'Tồn kho phải là số nguyên.',
                'stock.min' => 'Tồn kho không được âm.',

                // Validation for variants
                'variants.required' => 'Bạn phải tạo ít nhất một biến thể cho sản phẩm có biến thể.',
                'variants.array' => 'Dữ liệu biến thể không hợp lệ.',
                'variants.min' => 'Bạn phải tạo ít nhất một biến thể.',
                'variants.*.sku.required' => 'SKU biến thể là bắt buộc.',
                'variants.*.sku.unique' => 'SKU biến thể đã tồn tại.',
                'variants.*.price.required' => 'Giá biến thể là bắt buộc.',
                'variants.*.price.numeric' => 'Giá biến thể phải là số.',
                'variants.*.price.min' => 'Giá biến thể không được âm.',
                'variants.*.stock.required' => 'Tồn kho biến thể là bắt buộc.',
                'variants.*.stock.integer' => 'Tồn kho biến thể phải là số nguyên.',
                'variants.*.stock.min' => 'Tồn kho biến thể không được âm.',
                'variants.*.image.image' => 'Tệp ảnh biến thể phải là hình ảnh.',
                'variants.*.image.mimes' => 'Định dạng ảnh biến thể không hợp lệ.',
                'variants.*.image.max' => 'Kích thước ảnh biến thể không được vượt quá 2MB.',
                'variants.*.attribute_values.required' => 'Biến thể phải có ít nhất một giá trị thuộc tính.',
                'variants.*.attribute_values.array' => 'Giá trị thuộc tính biến thể phải là một mảng.',
                'variants.*.attribute_values.min' => 'Biến thể phải có ít nhất một giá trị thuộc tính.',
                'variants.*.attribute_values.*.exists' => 'Giá trị thuộc tính không hợp lệ.',

                // Validation for scent groups
                'scent_groups.array' => 'Dữ liệu nhóm hương không hợp lệ.',
                'scent_groups.*.id.required' => 'ID nhóm hương là bắt buộc.',
                'scent_groups.*.id.exists' => 'ID nhóm hương không hợp lệ.',
                'scent_groups.*.strength.required' => 'Độ mạnh nhóm hương là bắt buộc.',
                'scent_groups.*.strength.integer' => 'Độ mạnh nhóm hương phải là số nguyên.',
                'scent_groups.*.strength.min' => 'Độ mạnh nhóm hương phải từ 1 trở lên.',
                'scent_groups.*.strength.max' => 'Độ mạnh nhóm hương tối đa là 5.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi validation khi thêm sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        $uploadedFilePaths = []; // Array to track all uploaded file paths

        try {
            // Handle main product image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products/main_images', 'public');
                $uploadedFilePaths[] = $imagePath;
            }

            // Create product data
            $productData = [
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
                'description' => $validatedData['description'] ?? null,
                'gender' => $validatedData['gender'],
                'category_id' => $validatedData['category_id'],
                'brand_id' => $validatedData['brand_id'],
                'image' => $imagePath,
                'has_variants' => $validatedData['has_variants'],
                // Price and stock for product table should be null if has_variants is true,
                // as they will be managed by variants.
                // Otherwise, they are directly taken from the request for the default variant.
                'price' => !$validatedData['has_variants'] ? ($validatedData['price'] ?? 0) : null,
                'stock' => !$validatedData['has_variants'] ? ($validatedData['stock'] ?? 0) : null,
            ];

            $product = Product::create($productData);

            // Handle gallery images upload
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $galleryImagePath = $file->store('products/gallery_images', 'public');
                    $uploadedFilePaths[] = $galleryImagePath;
                    $product->images()->create([
                        'image_url' => $galleryImagePath,
                    ]);
                }
            }

            // --- Handle Variants ---
            if ($validatedData['has_variants']) {
                // If product has variants, create them based on the request
                foreach ($validatedData['variants'] as $variantData) {
                    $variantImagePath = null;
                    if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantImagePath = $variantData['image']->store('product_variants/images', 'public');
                        $uploadedFilePaths[] = $variantImagePath;
                    }

                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'image' => $variantImagePath,
                        'sold' => $variantData['sold'] ?? 0,
                        'status' => $variantData['status'] ?? 'available',
                        'barcode' => $variantData['barcode'] ?? null,
                        'description' => $variantData['description'] ?? null,
                    ]);

                    $variant->attributeValues()->sync($variantData['attribute_values']);
                }
            } else {
                // If product DOES NOT have variants, create a single default variant
                // Use the product's price and stock for this default variant
                // Generate a unique SKU for the default variant, e.g., using product slug
                $defaultSku = Str::slug($validatedData['name']) . '-' . Str::random(5);

                $product->variants()->create([
                    'sku' => $defaultSku,
                    'price' => $validatedData['price'] ?? 0, // Use product's price
                    'stock' => $validatedData['stock'] ?? 0, // Use product's stock
                    'image' => $imagePath, // Use main product image for default variant
                    'sold' => 0,
                    'status' => 'available',
                    'barcode' => null,
                    'description' => null, // Or can copy product description if desired
                ]);
            }
            // --- End Handle Variants ---

            // --- Handle Scent Groups ---
            if (isset($validatedData['scent_groups']) && is_array($validatedData['scent_groups'])) {
                $scentGroupSyncData = [];
                foreach ($validatedData['scent_groups'] as $scentGroup) {
                    $scentGroupSyncData[$scentGroup['id']] = ['strength' => $scentGroup['strength']];
                }
                $product->scentGroups()->sync($scentGroupSyncData);
            }
            // --- End Handle Scent Groups ---

            DB::commit();
            // Load relationships to return full data
            $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute', 'scentGroups']); // Load scentGroups

            return response()->json([
                'message' => 'Sản phẩm đã được thêm thành công!',
                'data' => new ProductDetailResource($product)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Delete all uploaded files if an error occurs
            foreach ($uploadedFilePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error("Lỗi khi thêm sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/admin/products/{product}
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute']);
        return new ProductDetailResource($product);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/admin/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string',
            'gender' => 'sometimes|in:male,female,unisex',
            'price' => 'sometimes|numeric|min:0', // Applied if not has_variants
            'stock' => 'sometimes|integer|min:0', // Applied if not has_variants
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Main product image
            'remove_main_image' => 'boolean', // Flag to delete main image

            // --- NEW: Validation for additional images and deletions ---
            'additional_images' => 'nullable|array', // New images to upload
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deleted_image_ids' => 'nullable|array', // IDs of existing images to delete
            'deleted_image_ids.*' => 'integer|exists:product_images,id', // Ensure IDs are integers and exist
            // -----------------------------------------------------------

            'has_variants' => 'sometimes|boolean',
            'variants' => 'nullable|json',
        ];

        // Apply rules for price/stock based on has_variants, if present in request
        if ($request->has('has_variants')) {
            if ($request->boolean('has_variants')) {
                $rules['price'] = 'nullable|numeric|min:0'; // Price/stock become nullable if variants exist
                $rules['stock'] = 'nullable|integer|min:0';
                $rules['variants'] = 'required|array|min:1'; // If true, variants are required
                $rules['variants.*.sku'] = ['required', 'string', 'max:255', Rule::unique('product_variants')->ignore($product->id, 'product_id')->where(function ($query) use ($product) {
                    return $query->where('product_id', $product->id); // Ensure uniqueness within this product's variants
                })]; // Complex rule for unique SKU per product
                $rules['variants.*.price'] = 'required|numeric|min:0';
                $rules['variants.*.stock'] = 'required|integer|min:0';
                $rules['variants.*.image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                $rules['variants.*.attribute_values'] = 'required|array|min:1';
                $rules['variants.*.attribute_values.*'] = 'exists:attribute_values,id';
            } else {
                $rules['price'] = 'required|numeric|min:0'; // Price/stock required if no variants
                $rules['stock'] = 'required|integer|min:0';
                $rules['variants'] = 'nullable|array|max:0'; // No variants allowed
            }
        }


        $validated = $request->validate($rules, [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'price.numeric' => 'Giá phải là một số.',
            'price.min' => 'Giá không được nhỏ hơn 0.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
            'variants.json' => 'Dữ liệu biến thể không hợp lệ.',
            'additional_images.*.image' => 'Tệp ảnh phụ phải là hình ảnh.',
            'additional_images.*.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, gif, svg.',
            'additional_images.*.max' => 'Kích thước ảnh phụ không được vượt quá 2MB.',
            'deleted_image_ids.*.exists' => 'Ảnh phụ cần xóa không tồn tại.',
        ]);

        DB::beginTransaction();
        try {
            $currentImagePath = $product->image;

            // Handle main image deletion
            if (isset($validated['remove_main_image']) && $validated['remove_main_image']) {
                if ($currentImagePath && Storage::disk('public')->exists($currentImagePath)) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $validated['image'] = null; // Set image to null in DB
            }

            // Handle main image upload/update
            if ($request->hasFile('image')) {
                if ($currentImagePath && Storage::disk('public')->exists($currentImagePath)) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            } else if (!isset($validated['image']) && !isset($validated['remove_main_image'])) {
                // If no new image and not removing, retain old image path
                $validated['image'] = $currentImagePath;
            }

            // Update slug if name changes
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Prepare product data for update (excluding image/variant specific fields handled below)
            $productDataForUpdate = collect($validated)->except([
                'image',
                'remove_main_image',
                'additional_images',
                'deleted_image_ids',
                'variants'
            ])->toArray();

            // Explicitly set price/stock to null if has_variants is true and they are not provided
            if (isset($validated['has_variants']) && $validated['has_variants']) {
                $productDataForUpdate['price'] = null;
                $productDataForUpdate['stock'] = null;
            }

            $product->update($productDataForUpdate);

            // --- NEW: Handle deleted additional images ---
            if (isset($validated['deleted_image_ids']) && is_array($validated['deleted_image_ids'])) {
                foreach ($validated['deleted_image_ids'] as $imageId) {
                    $productImage = ProductImage::find($imageId);
                    if ($productImage && $productImage->product_id === $product->id) { // Ensure ownership
                        if (Storage::disk('public')->exists($productImage->image_url)) {
                            Storage::disk('public')->delete($productImage->image_url);
                        }
                        $productImage->delete();
                    }
                }
            }

            // --- NEW: Handle new additional images upload ---
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $file) {
                    $additionalImagePath = $file->store('products/gallery', 'public');
                    $product->images()->create([
                        'image_url' => $additionalImagePath,
                    ]);
                }
            }
            // -----------------------------------------------

            // --- Handle Variants ---
            $submittedVariantsData = [];
            if (isset($validated['variants'])) {
                $submittedVariantsData = json_decode($validated['variants'], true);
                if (!is_array($submittedVariantsData)) {
                    throw new \Exception('Dữ liệu biến thể không phải là một mảng hợp lệ.');
                }
            }

            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $variantsToKeepIds = [];

            if (isset($validated['has_variants']) && $validated['has_variants']) { // If product should have variants
                foreach ($submittedVariantsData as $variantData) {
                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant) {
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

                            if (isset($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])) {
                                $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_value_ids'])->pluck('id');
                                $variant->attributeValues()->sync($validAttributeValueIds);
                            } else {
                                $variant->attributeValues()->detach();
                            }
                        }
                    } else { // New variant
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

                        if (isset($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])) {
                            $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_value_ids'])->pluck('id');
                            $newVariant->attributeValues()->attach($validAttributeValueIds);
                        }
                    }
                }
            } else { // If product should NOT have variants
                // Ensure all existing variants are soft-deleted
                $product->variants()->delete();
                $variantsToKeepIds = []; // No variants to keep
            }

            // Delete variants that were removed from the frontend (only if has_variants is true initially)
            if ($product->has_variants || ($request->has('has_variants') && !$request->boolean('has_variants'))) {
                ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $variantsToKeepIds)
                    ->delete();
            }

            DB::commit();

            // Reload the product with all necessary relations for the detailed response
            $product->load([
                'images',
                'category',
                'brand',
                'variants.attributeValues.attribute'
            ]);
            return new ProductDetailResource($product);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi validation khi cập nhật sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật sản phẩm. Vui lòng thử lại.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/admin/products/{product}
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id); // Tìm sản phẩm theo ID

            // Laravel sẽ tự động điền cột 'deleted_at' khi gọi delete()
            $product->delete();

            return response()->json([
                'message' => 'Sản phẩm đã được xóa mềm thành công!'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa mềm sản phẩm. Vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function trashed()
    {
        // Lấy tất cả sản phẩm đã xóa mềm
        $trashedProducts = Product::onlyTrashed()->get();

        // Bạn có thể trả về resource nếu cần
        // return ProductCollection::make($trashedProducts);
        return response()->json([
            'message' => 'Danh sách sản phẩm đã xóa mềm.',
            'data' => $trashedProducts
        ]);
    }

    public function restore(string $id)
    {
        try {
            // Tìm sản phẩm đã xóa mềm
            $product = Product::onlyTrashed()->findOrFail($id);

            // Khôi phục sản phẩm
            $product->restore();

            return response()->json([
                'message' => 'Sản phẩm đã được khôi phục thành công!'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi khôi phục sản phẩm. Vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        DB::beginTransaction(); // Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
        try {
            // 1. Tìm sản phẩm đã xóa mềm
            $product = Product::onlyTrashed()->findOrFail($id);

            // 2. XÓA CÁC FILE ẢNH VẬT LÝ TRƯỚC
            // Đây là phần quan trọng nhất để giải quyết vấn đề của bạn.

            // 2.1. Xóa ảnh chính của sản phẩm
            if ($product->image) { // Kiểm tra xem có ảnh chính không
                // Đường dẫn ảnh lưu trong DB là 'products/main_images/ten_file.jpg'
                // Storage::disk('public')->delete() sẽ tìm file trong 'storage/app/public/products/main_images/ten_file.jpg'
                if (Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                    Log::info("Đã xóa ảnh chính: " . $product->image);
                } else {
                    Log::warning("Không tìm thấy ảnh chính để xóa: " . $product->image);
                }
            }

            // 2.2. Xóa tất cả ảnh trong thư viện (gallery images)
            // Lấy các bản ghi ProductImage liên quan
            $galleryImages = $product->images;
            foreach ($galleryImages as $image) {
                // image_path là tên cột trong DB của ProductImage (vd: 'products/gallery_images/ten_file.jpg')
                if ($image->image_url) { // Đảm bảo có đường dẫn
                    // Đảm bảo loại bỏ tiền tố /storage/ nếu có
                    $galleryPathToDelete = str_replace('/storage/', '', $image->image_url);

                    if (Storage::disk('public')->exists($galleryPathToDelete)) {
                        Storage::disk('public')->delete($galleryPathToDelete);
                        Log::info("Đã xóa ảnh gallery: " . $galleryPathToDelete);
                    } else {
                        Log::warning("Không tìm thấy ảnh gallery để xóa: " . $galleryPathToDelete);
                    }
                } else {
                    Log::warning("Đường dẫn ảnh gallery trống cho ID: " . $image->id);
                }
            }

            // 2.3. Xóa ảnh của các biến thể (nếu có)
            $variants = $product->variants;
            foreach ($variants as $variant) {
                if ($variant->image) { // Kiểm tra xem biến thể có ảnh không
                    // variant->image là tên cột trong DB của ProductVariant (vd: 'product_variants/images/ten_file.jpg')
                    if (Storage::disk('public')->exists($variant->image)) {
                        Storage::disk('public')->delete($variant->image);
                        Log::info("Đã xóa ảnh biến thể: " . $variant->image);
                    } else {
                        Log::warning("Không tìm thấy ảnh biến thể để xóa: " . $variant->image);
                    }
                }
            }

            // 3. Xóa bản ghi trong database (SAU KHI XÓA FILE VẬT LÝ)
            // Khi gọi $product->forceDelete(), nó sẽ kích hoạt onDelete('cascade')
            // trên các mối quan hệ nếu bạn đã cấu hình đúng trong migration.
            // Điều này có nghĩa là các bản ghi ProductImage và ProductVariant liên quan
            // sẽ tự động bị xóa khỏi DB khi Product bị xóa cứng.
            $product->forceDelete();

            DB::commit(); // Hoàn tất transaction
            return response()->json([
                'message' => 'Sản phẩm và tất cả ảnh liên quan đã được xóa vĩnh viễn thành công!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction nếu có lỗi
            Log::error("Lỗi khi xóa vĩnh viễn sản phẩm và ảnh: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn sản phẩm. Vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- Consider removing these specific image upload/delete methods if they are merged into store/update ---
    // However, if you have very specific UI flows that require separate endpoints for single image operations,
    // you might keep them. For a typical product management, handling them in store/update is often simpler.

    /**
     * Upload / Update main image for a product.
     * POST /api/admin/products/{product}/image
     * (Consider merging this into the main 'update' method for simplicity)
     */
    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh chính.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ]);

        DB::beginTransaction();
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $path = $request->file('image')->store('products', 'public');
            $product->update(['image' => $path]);

            DB::commit();
            return response()->json([
                'message' => 'Ảnh chính đã được cập nhật thành công.',
                'image_url' => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật ảnh chính: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật ảnh chính.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload / Update additional images for a product.
     * POST /api/admin/products/{product}/images
     * WARNING: This method currently REPLACES all existing additional images.
     * (Consider merging this functionality into the main 'update' method for additive/selective changes)
     */
    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'images.required' => 'Vui lòng chọn ít nhất một ảnh phụ.',
            'images.min' => 'Vui lòng chọn ít nhất một ảnh phụ.',
            'images.*.image' => 'Tệp ảnh phụ phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Kích thước ảnh phụ không được vượt quá 2MB.',
        ]);

        DB::beginTransaction();
        try {
            // Delete all old additional images and their files
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
                $image->delete();
            }

            // Save new additional images
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create(['image_url' => $path]);
            }

            DB::commit();
            $product->load('images');
            return response()->json([
                'message' => 'Ảnh phụ đã được cập nhật thành công.',
                'data' => ProductImageResource::collection($product->images),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật ảnh phụ: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật ảnh phụ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a specific additional product image.
     * DELETE /api/admin/products/images/{imageId}
     * (Can be kept as a separate endpoint if needed for specific UI operations)
     */
    public function deleteImage(string $imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        DB::beginTransaction();
        try {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
            $image->delete();

            DB::commit();
            return response()->json(['message' => 'Ảnh phụ đã được xóa thành công.', 'image_id' => $imageId]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa ảnh phụ: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa ảnh phụ.', 'error' => $e->getMessage()], 500);
        }
    }
}
