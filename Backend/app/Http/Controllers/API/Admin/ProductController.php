<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductUsageProfile;
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
            $rules['variants.*.image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
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
                'image.mimes' => 'Định dạng ảnh chính không hợp lệ. Chỉ chấp nhận: jpeg, png, jpg, gif, svg.',
                'image.max' => 'Kích thước ảnh chính không được vượt quá 2MB.',
                'gallery_images.array' => 'Thư viện ảnh phải là một mảng.',
                'gallery_images.*.image' => 'Mỗi tệp trong thư viện ảnh phải là một hình ảnh.',
                'gallery_images.*.mimes' => 'Mỗi tệp trong thư viện ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
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
                'variants.*.image.image' => 'Tệp ảnh biến thể phải là hình ảnh.',
                'variants.*.image.mimes' => 'Định dạng ảnh biến thể không hợp lệ.',
                'variants.*.image.max' => 'Kích thước ảnh biến thể không được vượt quá 2MB.',
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
                        'image_url' => $galleryImagePath,
                    ]);
                }
            }

            // --- Handle Variants ---
            if ($validatedData['has_variants']) {
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
            'price' => 'sometimes|numeric|min:0', // Applied if not has_variants
            'stock' => 'sometimes|integer|min:0', // Applied if not has_variants
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Main product image
            'remove_main_image' => 'boolean', // Flag to delete main image

            'additional_images' => 'nullable|array', // New images to upload
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deleted_image_ids' => 'nullable|array', // IDs of existing images to delete
            'deleted_image_ids.*' => 'integer|exists:product_images,id',

            'has_variants' => 'sometimes|boolean',
            'variants' => 'nullable|json', // Variants data is sent as JSON string

            'scent_groups' => 'nullable|json', // New: Validation for scent groups (JSON string)

            // --- BỔ SUNG CÁC TRƯỜNG VALIDATION CHO USAGE PROFILE (UPDATE) ---
            'usage_profile' => 'nullable|array',
            'usage_profile.spring_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.summer_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.autumn_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.winter_percent' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_day' => 'nullable|integer|min:0|max:100',
            'usage_profile.suitable_night' => 'nullable|integer|min:0|max:100',
            'usage_profile.longevity_hours' => 'nullable|numeric|min:0|max:99.9',
            'usage_profile.sillage_range_m' => 'nullable|string|max:255',
            // --- KẾT THÚC BỔ SUNG ---
        ];

        // Apply rules for price/stock based on has_variants, if present in request
        if ($request->has('has_variants')) {
            if ($request->boolean('has_variants')) {
                $rules['price'] = 'nullable|numeric|min:0'; // Price/stock become nullable if variants exist
                $rules['stock'] = 'nullable|integer|min:0';
                $rules['variants'] = 'required|array|min:1'; // If true, variants are required
                $rules['variants.*.sku'] = [
                    'required',
                    'string',
                    'max:255',
                    // This unique rule needs to ignore the current variant ID if it exists in the payload,
                    // and ignore all other variants of this product. This is tricky.
                    // The simplest is to ensure uniqueness across all product_variants except the current one being updated.
                    Rule::unique('product_variants', 'sku')->ignore($product->id, 'product_id')->where(function ($query) use ($product) {
                        return $query->where('product_id', $product->id); // For variants of THIS product
                    })
                ];
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


        try {
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
                'variants.json' => 'Dữ liệu biến thể không hợp lệ.', // If variants sent as JSON string
                'additional_images.*.image' => 'Tệp ảnh phụ phải là hình ảnh.',
                'additional_images.*.mimes' => 'Ảnh phụ phải có định dạng: jpeg, png, jpg, gif, svg.',
                'additional_images.*.max' => 'Kích thước ảnh phụ không được vượt quá 2MB.',
                'deleted_image_ids.*.exists' => 'Ảnh phụ cần xóa không tồn tại.',

                // --- BỔ SUNG CÁC THÔNG BÁO LỖI CHO USAGE PROFILE (UPDATE) ---
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

            // Decode scent_groups JSON string for update
            $scentGroupsData = [];
            if (isset($validated['scent_groups'])) {
                $scentGroupsData = json_decode($validated['scent_groups'], true);
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
                'message' => 'Lỗi validation khi cập nhật sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        }


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
                'variants',
                'scent_groups', // Exclude as it's handled separately
                'usage_profile', // Exclude as it's handled separately
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
                    // This logic for updating/creating variants is complex with unique SKUs.
                    // It's generally better to handle it with a dedicated service or more robust method
                    // to prevent race conditions or unexpected unique constraint violations.
                    // For now, assuming `id` is present for existing variants and missing for new ones.
                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant) {
                            $variantValidated = validator($variantData, [
                                'sku' => ['required', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
                                'price' => 'required|numeric|min:0',
                                'stock' => 'required|integer|min:0',
                                'sold' => 'sometimes|integer|min:0',
                                'status' => 'sometimes|string',
                                'barcode' => 'nullable|string|max:255',
                                'description' => 'nullable|string',
                            ])->validate();

                            $variant->update($variantValidated);
                            $variantsToKeepIds[] = $variant->id;

                            if (isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])) {
                                $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_values'])->pluck('id');
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

                        if (isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])) {
                            $validAttributeValueIds = \App\Models\AttributeValue::whereIn('id', $variantData['attribute_values'])->pluck('id');
                            $newVariant->attributeValues()->attach($validAttributeValueIds);
                        }
                    }
                }
            } else { // If product should NOT have variants
                // Ensure all existing variants are deleted
                $product->variants()->delete();
                $variantsToKeepIds = []; // No variants to keep
            }

            // Delete variants that were removed from the frontend
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $variantsToKeepIds)
                ->delete();
            // --- End Handle Variants ---

            // --- Handle Scent Groups (Update) ---
            if (!empty($scentGroupsData)) {
                $scentGroupSyncData = [];
                foreach ($scentGroupsData as $scentGroupItem) { // Iterate over each item
                    // Ensure 'id' exists and is valid before using it
                    if (isset($scentGroupItem['id']) && $scentGroupItem['id'] > 0) {
                        $scentGroupId = $scentGroupItem['id'];
                        $strength = $scentGroupItem['strength'] ?? 50; // Use default if strength is missing
                        $scentGroupSyncData[$scentGroupId] = ['strength' => $strength];
                    } else {
                        // Optionally log or handle cases where an item doesn't have a valid ID
                        \Log::warning('Scent group item without valid ID encountered during sync preparation.', ['item' => $scentGroupItem]);
                    }
                }
                // \Log::info('Prepared scentGroupSyncData for sync:', ['data' => $scentGroupSyncData]); // Uncomment for further debugging
                $product->scentGroups()->sync($scentGroupSyncData);
            } else {
                $product->scentGroups()->detach(); // If no scent groups, remove all existing
            }
            // --- End Handle Scent Groups ---

            // --- BỔ SUNG: Handle Usage Profile (Update) ---
            if (isset($validated['usage_profile'])) {
                $usageProfileData = $validated['usage_profile'];
                $product->usageProfile()->updateOrCreate(
                    ['product_id' => $product->id], // Find by product_id
                    [
                        'spring_percent' => $usageProfileData['spring_percent'] ?? 0,
                        'summer_percent' => $usageProfileData['summer_percent'] ?? 0,
                        'autumn_percent' => $usageProfileData['autumn_percent'] ?? 0,
                        'winter_percent' => $usageProfileData['winter_percent'] ?? 0,
                        'suitable_day' => $usageProfileData['suitable_day'] ?? 0,
                        'suitable_night' => $usageProfileData['suitable_night'] ?? 0,
                        'longevity_hours' => $usageProfileData['longevity_hours'] ?? 0.0,
                        'sillage_range_m' => $usageProfileData['sillage_range_m'] ?? '',
                    ]
                );
            } else {
                // If usage_profile is not sent, you might want to delete it or keep it as is.
                // For now, let's delete it if no data is provided to ensure data integrity.
                $product->usageProfile()->delete();
            }
            // --- KẾT THÚC BỔ SUNG ---

            DB::commit();

            // Reload the product with all necessary relations for the detailed response
            $product->load([
                'images',
                'category',
                'brand',
                'variants.attributeValues.attribute',
                'scentGroups', // Reload scentGroups
                'usageProfile', // Reload usageProfile
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
