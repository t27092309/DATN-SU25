<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductUsageProfile;
use App\Models\ProductImage; // Essential for handling additional images
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeValue;
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
use Illuminate\Support\Facades\Validator;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Main product image
            'gallery_images' => 'nullable|array', // Allows an array of files
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096', // Each file in the array
            'has_variants' => 'required|boolean',
            'scent_groups' => 'nullable|json', // Changed to json, as frontend sends stringified JSON
            // 'scent_groups.*.id' => 'required|exists:scent_groups,id', // These rules are for array, need to validate after json_decode
            // 'scent_groups.*.strength' => 'required|integer|min:1|max:100', // Adjusted max for 1-100 scale

            // --- BỔ SUNG CÁC TRƯỜNG VALIDATION CHO USAGE PROFILE ---
            'usage_profile' => 'nullable|array',
            'usage_profile.spring_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.summer_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.autumn_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.winter_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_day' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_night' => 'nullable|integer|min:0|max:100',
            'usage_profile.longevity_hours' => 'nullable|numeric|min:0|max:99.9', // Max based on 3,1 decimal
            'usage_profile.sillage_range_m' => 'nullable|string|max:255',
            // --- KẾT THÚC BỔ SUNG ---
        ];

        // Add validation rules depending on product type (with/without variants)
        if ($request->boolean('has_variants')) {
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.sku'] = 'required|string|max:255|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.attribute_values'] = 'required|array|min:1';
            $rules['variants.*.attribute_values.*'] = 'exists:attribute_values,id';
        } else {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
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
                'image.mimes' => 'Định dạng ảnh chính không hợp lệ. Chỉ chấp nhận: jpeg, png, jpg, gif, svg, webp.',
                'image.max' => 'Kích thước ảnh chính không được vượt quá 2MB.',
                'gallery_images.array' => 'Thư viện ảnh phải là một mảng.',
                'gallery_images.*.image' => 'Mỗi tệp trong thư viện ảnh phải là một hình ảnh.',
                'gallery_images.*.mimes' => 'Mỗi tệp trong thư viện ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'gallery_images.*.max' => 'Mỗi tệp trong thư viện ảnh không được lớn hơn 4MB.',
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
                'variants.*.attribute_values.required' => 'Biến thể phải có ít nhất một giá trị thuộc tính.',
                'variants.*.attribute_values.array' => 'Giá trị thuộc tính biến thể phải là một mảng.',
                'variants.*.attribute_values.min' => 'Biến thể phải có ít nhất một giá trị thuộc tính.',
                'variants.*.attribute_values.*.exists' => 'Giá trị thuộc tính không hợp lệ.',

                // Validation for scent groups
                'scent_groups.json' => 'Dữ liệu nhóm hương không hợp lệ (phải là JSON string).',

                // --- BỔ SUNG CÁC THÔNG BÁO LỖI CHO USAGE PROFILE ---
                'usage_profile.array' => 'Dữ liệu hồ sơ sử dụng không hợp lệ.',
                'usage_profile.spring_percent.integer' => 'Phần trăm mùa xuân phải là số nguyên.',
                'usage_profile.spring_percent.min' => 'Phần trăm mùa xuân phải từ 0 trở lên.',
                'usage_profile.spring_percent.max' => 'Phần trăm mùa xuân tối đa là 100.',
                'usage_profile.summer_percent.integer' => 'Phần trăm mùa hè phải là số nguyên.',
                'usage_profile.summer_percent.min' => 'Phần trăm mùa hè phải từ 0 trở lên.',
                'usage_profile.summer_percent.max' => 'Phần trăm mùa hè tối đa là 100.',
                'usage_profile.autumn_percent.integer' => 'Phần trăm mùa thu phải là số nguyên.',
                'usage_profile.autumn_percent.min' => 'Phần trăm mùa thu phải từ 0 trở lên.',
                'usage_profile.autumn_percent.max' => 'Phần trăm mùa thu tối đa là 100.',
                'usage_profile.winter_percent.integer' => 'Phần trăm mùa đông phải là số nguyên.',
                'usage_profile.winter_percent.min' => 'Phần trăm mùa đông phải từ 0 trở lên.',
                'usage_profile.winter_percent.max' => 'Phần trăm mùa đông tối đa là 100.',
                'usage_profile.suitable_day.integer' => 'Phần trăm ban ngày phải là số nguyên.',
                'usage_profile.suitable_day.min' => 'Phần trăm ban ngày phải từ 0 trở lên.',
                'usage_profile.suitable_day.max' => 'Phần trăm ban ngày tối đa là 100.',
                'usage_profile.suitable_night.integer' => 'Phần trăm ban đêm phải là số nguyên.',
                'usage_profile.suitable_night.min' => 'Phần trăm ban đêm phải từ 0 trở lên.',
                'usage_profile.suitable_night.max' => 'Phần trăm ban đêm tối đa là 100.',
                'usage_profile.longevity_hours.numeric' => 'Thời gian lưu hương phải là số.',
                'usage_profile.longevity_hours.min' => 'Thời gian lưu hương không được âm.',
                'usage_profile.longevity_hours.max' => 'Thời gian lưu hương không được vượt quá 99.9 giờ.',
                'usage_profile.sillage_range_m.string' => 'Độ tỏa hương phải là chuỗi ký tự.',
                'usage_profile.sillage_range_m.max' => 'Độ tỏa hương không được vượt quá 255 ký tự.',
                // --- KẾT THÚC BỔ SUNG THÔNG BÁO LỖI ---
            ]);

            // Decode scent_groups JSON string
            $scentGroupsData = [];
            if (isset($validatedData['scent_groups'])) {
                $scentGroupsData = json_decode($validatedData['scent_groups'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw ValidationException::withMessages(['scent_groups' => 'Dữ liệu nhóm hương không phải là JSON hợp lệ.']);
                }
                // Validate the structure of decoded scent_groups data
                $validator = validator($scentGroupsData, [
                    '*.strength' => 'required|integer|min:1|max:100', // Adjusted max for 1-100 scale
                ], [
                    '*.strength.required' => 'Độ mạnh nhóm hương là bắt buộc.',
                    '*.strength.integer' => 'Độ mạnh nhóm hương phải là số nguyên.',
                    '*.strength.min' => 'Độ mạnh nhóm hương phải từ 1 trở lên.',
                    '*.strength.max' => 'Độ mạnh nhóm hương tối đa là 100.',
                ]);
                $validator->validate(); // This will throw ValidationException on failure
            }
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
                        'path' => $galleryImagePath,
                    ]);
                }
            }

            // --- Handle Variants ---
            if ($validatedData['has_variants']) {
                foreach ($validatedData['variants'] as $variantData) {
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'sold' => $variantData['sold'] ?? 0,
                        'status' => $variantData['status'] ?? 'available',
                        'barcode' => $variantData['barcode'] ?? null,
                        'description' => $variantData['description'] ?? null,
                    ]);

                    $variant->attributeValues()->sync($variantData['attribute_values']);
                }
            } else {
                $defaultSku = Str::slug($validatedData['name']) . '-' . Str::random(5);

                $product->variants()->create([
                    'sku' => $defaultSku,
                    'price' => $validatedData['price'] ?? 0,
                    'stock' => $validatedData['stock'] ?? 0,
                    'image' => $imagePath,
                    'sold' => 0,
                    'status' => 'available',
                    'barcode' => null,
                    'description' => null,
                ]);
            }
            // --- End Handle Variants ---

            // --- Handle Scent Groups ---
            if (!empty($scentGroupsData)) {
                $scentGroupSyncData = [];
                foreach ($scentGroupsData as $scentGroupId => $data) {
                    // $scentGroupId is the key from decoded JSON, which is the ID
                    $scentGroupSyncData[$scentGroupId] = ['strength' => $data['strength']];
                }
                $product->scentGroups()->sync($scentGroupSyncData);
            } else {
                $product->scentGroups()->detach(); // If no scent groups, remove all existing
            }
            // --- End Handle Scent Groups ---

            // --- BỔ SUNG: Handle Usage Profile ---
            if (isset($validatedData['usage_profile'])) {
                $usageProfileData = $validatedData['usage_profile'];
                // Create a new usage profile entry
                $product->usageProfile()->create([
                    'spring_percent' => $usageProfileData['spring_percent'] ?? 0,
                    'summer_percent' => $usageProfileData['summer_percent'] ?? 0,
                    'autumn_percent' => $usageProfileData['autumn_percent'] ?? 0,
                    'winter_percent' => $usageProfileData['winter_percent'] ?? 0,
                    'suitable_day' => $usageProfileData['suitable_day'] ?? 0,
                    'suitable_night' => $usageProfileData['suitable_night'] ?? 0,
                    'longevity_hours' => $usageProfileData['longevity_hours'] ?? 0.0,
                    'sillage_range_m' => $usageProfileData['sillage_range_m'] ?? '',
                ]);
            }
            // --- KẾT THÚC BỔ SUNG ---

            DB::commit();
            // Load relationships to return full data
            $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute', 'scentGroups', 'usageProfile']); // Load usageProfile

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
        $product->load([
            'category',
            'brand',
            'images',
            'variants.attributeValues.attribute',
            'usageProfile',
            // 'scentGroups',
            'scentProfiles.scentGroup',
        ]);
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
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image' => [
                'nullable',
                'sometimes',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->hasFile('image')) {
                        Validator::make(
                            $request->only('image'),
                            ['image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048']
                        )->validate();
                    }
                },
            ],
            'remove_main_image' => 'nullable|boolean',
            'new_additional_images' => 'nullable|array',
            'new_additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'gallery_images_order' => 'nullable|array',
            'gallery_images_order.*.id' => 'required|integer|exists:product_images,id',
            'gallery_images_order.*.order' => 'required|integer|min:0',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer|exists:product_images,id',
            'has_variants' => 'sometimes|boolean',
            'scent_groups' => 'nullable|json',
            'usage_profile' => 'nullable|array',
            'usage_profile.spring_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.summer_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.autumn_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.winter_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_day' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_night' => 'nullable|integer|min:0|max:100',
            'usage_profile.longevity_hours' => 'nullable|numeric|min:0|max:99.9',
            'usage_profile.sillage_range_m' => 'nullable|string|max:255',
        ];

        // if ($request->has('has_variants')) {
        //     if ($request->boolean('has_variants')) {
        //         $rules['price'] = 'nullable|numeric|min:0';
        //         $rules['stock'] = 'nullable|integer|min:0';
        //         $rules['variants'] = 'required|json';
        //     } else {
        //         $rules['price'] = 'required|numeric|min:0';
        //         $rules['stock'] = 'required|integer|min:0';
        //         $rules['variants'] = 'nullable|string';
        //     }
        // }

        if ($request->boolean('has_variants')) {
            $rules['variants'] = 'required|json';
        } elseif ($request->has('variants')) {
            $rules['variants'] = 'nullable|json';
        } else {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
        }

        try {
            $validated = $request->validate($rules);
            $submittedVariantsData = [];
            if (isset($validated['variants'])) {
                $submittedVariantsData = json_decode($validated['variants'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Dữ liệu biến thể không phải là một mảng JSON hợp lệ.');
                }

                foreach ($submittedVariantsData as $variantIndex => $variantData) {
                    $variantValidator = Validator::make($variantData, [
                        'id' => 'nullable|integer|exists:product_variants,id',
                        'sku' => [
                            'required',
                            'string',
                            'max:255',
                            Rule::unique('product_variants', 'sku')->ignore($variantData['id'] ?? null)
                        ],
                        'price' => 'required|numeric|min:0',
                        'stock' => 'required|integer|min:0',
                        'active' => 'required|boolean',
                    ]);

                    $variantValidator->validate();

                    // Validate attribute_values nếu có
                    if (array_key_exists('attribute_values', $variantData)) {
                        if (!is_array($variantData['attribute_values'])) {
                            throw ValidationException::withMessages([
                                "variants.$variantIndex.attribute_values" => 'attribute_values phải là mảng.'
                            ]);
                        }

                        foreach ($variantData['attribute_values'] as $valueId) {
                            if (!AttributeValue::where('id', $valueId)->exists()) {
                                throw ValidationException::withMessages([
                                    "variants.$variantIndex.attribute_values" => "Giá trị thuộc tính ID $valueId không tồn tại."
                                ]);
                            }
                        }
                    }
                }
            }
            /////////////////////////
            $scentGroupsData = [];
            if (isset($validated['scent_groups'])) {
                $scentGroupsData = json_decode($validated['scent_groups'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw ValidationException::withMessages(['scent_groups' => 'Dữ liệu nhóm hương không phải là JSON hợp lệ.']);
                }
                $validator = Validator::make($scentGroupsData, [
                    '*.id' => 'required|exists:scent_groups,id',
                    '*.strength' => 'required|integer|min:1|max:100',
                ], [
                    '*.id.required' => 'ID nhóm hương là bắt buộc.',
                    '*.id.exists' => 'ID nhóm hương không tồn tại.',
                    '*.strength.required' => 'Độ mạnh nhóm hương là bắt buộc.',
                    '*.strength.integer' => 'Độ mạnh nhóm hương phải là số nguyên.',
                    '*.strength.min' => 'Độ mạnh nhóm hương phải từ 1 trở lên.',
                    '*.strength.max' => 'Độ mạnh nhóm hương tối đa là 100.',
                ]);
                $validator->validate();
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi validation khi cập nhật sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Lỗi decoding JSON hoặc validation tùy chỉnh: " . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi xử lý dữ liệu. Vui lòng kiểm tra lại định dạng gửi lên.',
                'error' => $e->getMessage()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $productDataForUpdate = collect($validated)->except([
                'image',
                'remove_main_image',
                'new_additional_images',
                'gallery_images_order',
                'deleted_image_ids',
                'variants',
                'scent_groups',
                'usage_profile',
            ])->toArray();


            Log::info("🔹 Bắt đầu xử lý ảnh chính sản phẩm #{$product->id}", [
                'hasFile'      => $request->hasFile('image'),
                'removeMain'   => $request->boolean('remove_main_image'),
                'inputImage'   => $request->input('image'),
                'currentImage' => $product->image,
            ]);

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                    Log::info("🗑️ Xóa ảnh cũ: {$product->image}");
                }
                $productDataForUpdate['image'] = $request->file('image')->store('products/main_images', 'public');
                Log::info("✅ Upload ảnh mới: {$productDataForUpdate['image']}");
            } elseif ($request->boolean('remove_main_image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                    Log::info("🗑️ Xóa ảnh chính theo remove_main_image");
                }
                $productDataForUpdate['image'] = null;
            } else {
                // Không upload, không xoá → giữ nguyên ảnh cũ
                unset($productDataForUpdate['image']);
                Log::info("ℹ️ Giữ nguyên ảnh chính: " . ($product->image ?? 'null'));
            }

            $product->update($productDataForUpdate);

            // --- Cải thiện: Xử lý ảnh Gallery một cách an toàn ---

            // 1. Xóa ảnh
            if (isset($validated['deleted_image_ids'])) {
                $imagesToDelete = $product->images()->whereIn('id', $validated['deleted_image_ids'])->get();

                Log::info("🗑️ Xóa các ảnh gallery với IDs:", $validated['deleted_image_ids']);

                foreach ($imagesToDelete as $image) {
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                        Log::info("🗑️ Đã xóa file ảnh: {$image->path}");
                    }
                    $image->delete();
                    Log::info("🗑️ Đã xóa record ảnh với ID: {$image->id} trong DB");
                }

                $remainingImagesAfterDelete = $product->images()->pluck('id')->toArray();
                Log::info("✅ Ảnh gallery còn lại sau khi xóa:", $remainingImagesAfterDelete);
            }

            // 2. Cập nhật thứ tự ảnh còn lại
            if (isset($validated['gallery_images_order'])) {
                // Nếu gallery_images_order là chuỗi JSON, decode nó
                if (is_string($validated['gallery_images_order'])) {
                    $galleryOrder = json_decode($validated['gallery_images_order'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('❌ Lỗi decode JSON gallery_images_order: ' . json_last_error_msg());
                        $galleryOrder = [];
                    }
                } else {
                    $galleryOrder = $validated['gallery_images_order'];
                }

                foreach ($galleryOrder as $item) {
                    ProductImage::where('id', $item['id'])
                        ->where('product_id', $product->id)
                        ->update(['order' => $item['order']]);
                    Log::info("🔄 Cập nhật order ảnh ID {$item['id']} thành {$item['order']}");
                }
            }

            // 3. Thêm ảnh mới
            if ($request->hasFile('new_additional_images')) {
                $newImages = [];
                foreach ($request->file('new_additional_images') as $file) {
                    $newPath = $file->store('products/gallery', 'public');
                    $newImages[] = ['path' => $newPath, 'product_id' => $product->id];
                    Log::info("➕ Upload ảnh mới: {$newPath}");
                }
                ProductImage::insert($newImages);
                Log::info("✅ Đã thêm " . count($newImages) . " ảnh mới vào gallery");
            }

            // --- Kết thúc xử lý Gallery ---

            // --- Cải thiện: Logic cập nhật Variants hiệu quả hơn ---
            // --- Xử lý cập nhật Variants (an toàn, tránh mất hết biến thể khi không gửi variants) ---
            $existingVariants = $product->variants()->get();

            if (is_array($submittedVariantsData) && count($submittedVariantsData) > 0) {
                $touchedIds = [];

                foreach ($submittedVariantsData as $variantData) {
                    $attrsToSync = isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])
                        ? $variantData['attribute_values'] : null;
                    unset($variantData['attribute_values']);

                    // Ép kiểu active về 0/1
                    $variantData['active'] = (int) filter_var($variantData['active'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if (!empty($variantData['id'])) {
                        // Update variant cũ
                        $variant = $existingVariants->firstWhere('id', $variantData['id']);
                        if ($variant) {
                            $variant->update($variantData);
                            if (is_array($attrsToSync)) {
                                $variant->attributeValues()->sync($attrsToSync);
                            }
                            $touchedIds[] = $variant->id;
                        }
                    } else {
                        // Tạo variant mới
                        $newVariant = $product->variants()->create($variantData);
                        if (is_array($attrsToSync)) {
                            $newVariant->attributeValues()->sync($attrsToSync);
                        }
                        $touchedIds[] = $newVariant->id;
                    }
                }

                // Tắt các biến thể không có trong danh sách FE gửi (tương đương "bỏ tick")
                if (!empty($touchedIds)) {
                    $product->variants()->whereNotIn('id', $touchedIds)->update(['active' => 0]);
                }
            }
            // Cập nhật hoặc tạo mới các biến thể từ request

            foreach ($submittedVariantsData as $variantData) {
                $variantId = $variantData['id'] ?? null;

                // Kiểm tra xem attribute_values có được gửi và không rỗng
                $hasAttrValuesKey = array_key_exists('attribute_values', $variantData);
                $attributesToSync = $hasAttrValuesKey && is_array($variantData['attribute_values']) && !empty($variantData['attribute_values'])
                    ? $variantData['attribute_values']
                    : null;

                unset($variantData['attribute_values']); // Tránh fill vào cột không tồn tại
                if (isset($variantData['active'])) {
                    $variantData['active'] = filter_var($variantData['active'], FILTER_VALIDATE_BOOLEAN);
                }
                if ($variantId) {
                    $variant = $existingVariants->firstWhere('id', $variantId);
                    if ($variant) {
                        // Cập nhật thông tin biến thể
                        $variant->update($variantData);


                        // Chỉ đồng bộ attribute_values nếu được gửi và không rỗng
                        if ($hasAttrValuesKey) {
                            $variant->attributeValues()->sync($attributesToSync ?? []);
                        }
                        // Nếu không gửi attribute_values hoặc mảng rỗng, giữ nguyên giá trị cũ
                    }
                } else {
                    // Tạo biến thể mới
                    $newVariant = $product->variants()->create($variantData);

                    // Chỉ đồng bộ attribute_values nếu được gửi và không rỗng
                    if ($attributesToSync !== null) {
                        $newVariant->attributeValues()->sync($attributesToSync);
                    }
                    // Nếu không gửi, để trống (hoặc không sync)
                }
            }


            // --- Kết thúc xử lý Variants ---

            // --- Handle Scent Groups (Update) ---
            if ($request->filled('scent_groups')) {
                $raw = $request->input('scent_groups');
                Log::info("🔹 Raw scent_groups từ FE:", [$raw]); // log chuỗi JSON gốc

                $scentGroupsData = json_decode($raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("❌ Invalid scent_groups JSON", [
                        'error' => json_last_error_msg(),
                        'raw'   => $raw
                    ]);
                } else {
                    Log::info("✅ Parsed scent_groups:", $scentGroupsData);

                    // Xóa hết cũ
                    $deleted = $product->scentProfiles()->delete();
                    Log::info("🗑️ Đã xóa $deleted scent_profiles cũ cho product_id={$product->id}");

                    foreach ($scentGroupsData as $item) {
                        if (isset($item['id']) && (int)$item['id'] > 0) {
                            Log::info("➕ Thêm scent_group", [
                                'product_id'     => $product->id,
                                'scent_group_id' => $item['id'],
                                'strength'       => $item['strength'] ?? 50,
                            ]);

                            $product->scentProfiles()->create([
                                'scent_group_id' => $item['id'],
                                'strength'       => $item['strength'] ?? 50,
                            ]);
                        } else {
                            Log::warning("⚠️ Bỏ qua scent_group không hợp lệ", $item);
                        }
                    }
                }
            } else {
                Log::info("⚠️ Request không có field scent_groups");
            }
            // --- End Handle Scent Groups ---

            // --- Handle Usage Profile (Update) ---

            // --- Handle Usage Profile (Update) ---
            if ($request->has('usage_profile')) {
                $usageProfileData = $request->input('usage_profile', []);

                $product->usageProfile()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'spring_percent'   => $usageProfileData['spring_percent']   ?? $product->usageProfile->spring_percent   ?? 0,
                        'summer_percent'   => $usageProfileData['summer_percent']   ?? $product->usageProfile->summer_percent   ?? 0,
                        'autumn_percent'   => $usageProfileData['autumn_percent']   ?? $product->usageProfile->autumn_percent   ?? 0,
                        'winter_percent'   => $usageProfileData['winter_percent']   ?? $product->usageProfile->winter_percent   ?? 0,
                        'suitable_day'     => $usageProfileData['suitable_day']     ?? $product->usageProfile->suitable_day     ?? 0,
                        'suitable_night'   => $usageProfileData['suitable_night']   ?? $product->usageProfile->suitable_night   ?? 0,
                        'longevity_hours'  => $usageProfileData['longevity_hours']  ?? $product->usageProfile->longevity_hours  ?? 0.0,
                        'sillage_range_m'  => $usageProfileData['sillage_range_m']  ?? $product->usageProfile->sillage_range_m  ?? '',
                    ]
                );
            }

            // --- End Handle Usage Profile ---

            DB::commit();
            $product->load([
                'images',
                'category',
                'brand',
                'variants.attributeValues.attribute',
                'scentGroups',
                'usageProfile',
            ]);
            return response()->json([
                'message' => 'Sản phẩm đã được cập nhật thành công!',
                'data'    => new ProductDetailResource($product),
            ], 200);
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
                if ($image->path) { // Đảm bảo có đường dẫn
                    // Đảm bảo loại bỏ tiền tố /storage/ nếu có
                    $galleryPathToDelete = str_replace('/storage/', '', $image->path);

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh chính.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
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
                'path' => Storage::url($path),
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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'images.required' => 'Vui lòng chọn ít nhất một ảnh phụ.',
            'images.min' => 'Vui lòng chọn ít nhất một ảnh phụ.',
            'images.*.image' => 'Tệp ảnh phụ phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
            'images.*.max' => 'Kích thước ảnh phụ không được vượt quá 2MB.',
        ]);

        DB::beginTransaction();
        try {
            // Delete all old additional images and their files
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            // Save new additional images
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create(['path' => $path]);
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
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
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
