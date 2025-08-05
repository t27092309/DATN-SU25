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
                        'path' => $galleryImagePath,
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

            // --- CẬP NHẬT: Thêm validation cho gallery_images (bao gồm cả ảnh cũ và thứ tự)
            // 'gallery_images' => 'nullable|array', // Mảng các đối tượng {id: ..., order: ..., path: ...} hoặc chỉ order cho ảnh mới
            // 'gallery_images.*.id' => 'nullable|integer|exists:product_images,id', // ID của ảnh hiện có
            // 'gallery_images.*.order' => 'required|integer|min:0', // Thứ tự của ảnh, bắt buộc cho cả ảnh cũ và ảnh mới
            // 'gallery_images.*.path' => 'nullable|string', // Đường dẫn của ảnh cũ (không cần validate nếu đã validate id)

            // Dùng một trường riêng cho ảnh mới được upload lên
            'new_additional_images' => 'nullable|array',
            'new_additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

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
                    // The unique rule needs to consider existing variants being updated vs. new ones.
                    // This rule should ensure uniqueness across all variants of THIS product,
                    // ignoring the current variant if its ID is present in the request.
                    Rule::unique('product_variants', 'sku')->ignore($product->id, 'product_id')->where(function ($query) use ($product) {
                        return $query->where('product_id', $product->id);
                    })
                ];
                $rules['variants.*.price'] = 'required|numeric|min:0';
                $rules['variants.*.stock'] = 'required|integer|min:0';
                // No 'image' for variants here, as it's typically handled as an additional image if needed.
                // Or you can add it back if variants can have their own main images.
                // $rules['variants.*.image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
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

                // --- CẬP NHẬT: Thông báo lỗi cho gallery_images ---
                // 'gallery_images.array' => 'Dữ liệu ảnh phụ không hợp lệ.',
                // 'gallery_images.*.id.exists' => 'Ảnh phụ cần cập nhật không tồn tại.',
                // 'gallery_images.*.order.required' => 'Thứ tự ảnh phụ là bắt buộc.',
                // 'gallery_images.*.order.integer' => 'Thứ tự ảnh phụ phải là số nguyên.',
                // 'gallery_images.*.order.min' => 'Thứ tự ảnh phụ không được nhỏ hơn 0.',

                'new_additional_images.*.image' => 'Tệp ảnh mới phải là hình ảnh.',
                'new_additional_images.*.mimes' => 'Ảnh mới phải có định dạng: jpeg, png, jpg, gif, svg.',
                'new_additional_images.*.max' => 'Kích thước ảnh mới không được vượt quá 2MB.',
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
                    '*.id' => 'required|exists:scent_groups,id', // Make sure scent group ID exists
                    '*.strength' => 'required|integer|min:1|max:100', // Adjusted max for 1-100 scale
                ], [
                    '*.id.required' => 'ID nhóm hương là bắt buộc.',
                    '*.id.exists' => 'ID nhóm hương không tồn tại.',
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
            $imagePathForDb = null;

            // 1. Handle main image deletion request
            if ($request->has('remove_main_image') && $request->boolean('remove_main_image')) {
                if ($currentImagePath && Storage::disk('public')->exists($currentImagePath)) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $imagePathForDb = null; // Explicitly set to null for DB
            }

            // 2. Handle main image upload/update
            if ($request->hasFile('image')) {
                if ($currentImagePath && Storage::disk('public')->exists($currentImagePath) && $imagePathForDb !== null) {
                    Storage::disk('public')->delete($currentImagePath);
                }
                $imagePathForDb = $request->file('image')->store('products', 'public');
            }

            // Update slug if name changes
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Prepare product data for update (excluding image/variant/gallery specific fields handled below)
            $productDataForUpdate = collect($validated)->except([
                'image', // EXCLUDE 'image' by default from $validated
                'remove_main_image',
                'gallery_images', // EXCLUDE new 'gallery_images' field
                'new_additional_images', // EXCLUDE new 'new_additional_images' field
                'deleted_image_ids',
                'variants',
                'scent_groups',
                'usage_profile',
            ])->toArray();

            // ONLY add 'image' to $productDataForUpdate if $imagePathForDb was explicitly set
            if ($imagePathForDb !== null || ($request->has('remove_main_image') && $request->boolean('remove_main_image')) || $request->hasFile('image')) {
                $productDataForUpdate['image'] = $imagePathForDb;
            }

            // Explicitly set price/stock to null if has_variants is true and they are not provided
            if (isset($validated['has_variants']) && $validated['has_variants']) {
                $productDataForUpdate['price'] = null;
                $productDataForUpdate['stock'] = null;
            }

            $product->update($productDataForUpdate);

            // --- CẬP NHẬT: Xử lý ảnh Gallery (bao gồm xóa, thêm mới và cập nhật thứ tự) ---

            // Step 1: Xử lý ảnh bị xóa
            if (isset($validated['deleted_image_ids']) && is_array($validated['deleted_image_ids'])) {
                $product->images()->whereIn('id', $validated['deleted_image_ids'])->get()->each(function ($image) {
                    if (Storage::disk('public')->exists($image->path)) { // Sử dụng cột 'path'
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                });
            }

            // Step 2: Lấy danh sách các ID ảnh cũ còn lại từ request để xử lý order
            $existingGalleryImages = [];
            $newImagesToCreate = []; // Sẽ chứa ảnh mới upload với thứ tự của chúng

            if (isset($validated['gallery_images']) && is_array($validated['gallery_images'])) {
                foreach ($validated['gallery_images'] as $galleryImage) {
                    // Nếu có ID, đây là ảnh cũ, lưu lại ID và order
                    if (isset($galleryImage['id']) && $galleryImage['id'] !== null) {
                        $existingGalleryImages[$galleryImage['id']] = $galleryImage['order'];
                    }
                    // Nếu không có ID nhưng có path (trong trường hợp tái sắp xếp ảnh cũ mà không upload mới),
                    // chúng ta không xử lý ở đây vì đã xử lý ảnh cũ bằng ID.
                    // Chỉ xử lý các ảnh mới được upload ở bước tiếp theo.
                }
            }

            // Cập nhật thứ tự cho các ảnh cũ còn lại
            foreach ($existingGalleryImages as $imageId => $order) {
                ProductImage::where('id', $imageId)
                    ->where('product_id', $product->id) // Đảm bảo quyền sở hữu
                    ->update(['order' => $order]);
            }

            // Step 3: Xử lý ảnh mới được upload và gán thứ tự của chúng
            // $request->file('new_additional_images') sẽ chứa các file thực tế
            if ($request->hasFile('new_additional_images')) {
                foreach ($request->file('new_additional_images') as $index => $file) {
                    $newPath = $file->store('products/gallery', 'public');

                    // Lấy thứ tự từ mảng gallery_images theo index của file
                    // Frontend should send gallery_images array with placeholders for new files,
                    // or match orders explicitly. Assuming the order in new_additional_images corresponds
                    // to the order in the 'gallery_images' validation array for new items.
                    // A more robust way: Frontend sends gallery_images with a temporary_id for new files,
                    // and you match based on that. For simplicity here, we'll try to get order based on sequence.

                    $correspondingGalleryItem = null;
                    if (isset($validated['gallery_images']) && is_array($validated['gallery_images'])) {
                        // Find the gallery_images entry that corresponds to this new file.
                        // This assumes the frontend sends gallery_images with a structure that allows
                        // matching new files to their intended order.
                        // For example, frontend might send:
                        // gallery_images: [{id: 1, order: 0}, {order: 1, new_file_index: 0}, {id: 2, order: 2}]
                        // new_additional_images: [file0, file1]
                        // We need a way to link file0 to {order: 1, new_file_index: 0}.
                        // A simpler approach for now: assign arbitrary high order or sort after all uploads.

                        // If the frontend sends the order of NEW files within `gallery_images` and `new_additional_images`
                        // maintains that order, we can try to link by the "index" or sequentially.
                        // A more reliable way is to have `gallery_images` contain a `temp_id` for new files,
                        // and `new_additional_images` also send files with their `temp_id`.
                        // For this example, let's just assign an order based on the current highest order + 1
                        // or find the corresponding order from $validated['gallery_images'] if a direct match exists.

                        // Simplistic approach: if `gallery_images` contains entries without `id` but with `order`,
                        // we can try to pair them. This requires frontend to send correct counts.
                        // Let's iterate through $validated['gallery_images'] and find an entry without an 'id'
                        // that hasn't been processed yet.
                        foreach ($validated['gallery_images'] as $gIndex => $gItem) {
                            if (!isset($gItem['id']) && !isset($gItem['processed'])) { // Not an existing image and not yet processed
                                $newImagesToCreate[] = [
                                    'path' => $newPath, // Use 'path' column
                                    'order' => $gItem['order'],
                                    // Mark as processed to avoid reusing this slot for another new file
                                    // This assumes `gallery_images` contains one entry per *intended final image*.
                                    'processed' => true // This is just for internal loop tracking
                                ];
                                $validated['gallery_images'][$gIndex]['processed'] = true; // Mark original validation item
                                break; // Found a slot for this new file, move to next file
                            }
                        }
                    } else {
                        // Fallback: if no structured gallery_images, just add with high order.
                        // This might not be suitable for precise ordering.
                        $newImagesToCreate[] = [
                            'path' => $newPath,
                            'order' => $product->images()->count() + $index, // Simple sequential order
                        ];
                    }
                }
            }

            // Create new ProductImage records with their assigned order
            foreach ($newImagesToCreate as $imageData) {
                $product->images()->create([
                    'path' => $imageData['path'], // Use 'path' column
                    'order' => $imageData['order'],
                ]);
            }
            // --- KẾT THÚC CẬP NHẬT XỬ LÝ ẢNH GALLERY ---

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
                                $validAttributeValueIds = AttributeValue::whereIn('id', $variantData['attribute_values'])->pluck('id');
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
                            $validAttributeValueIds = AttributeValue::whereIn('id', $variantData['attribute_values'])->pluck('id');
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
                    if (isset($scentGroupItem['id']) && $scentGroupItem['id'] > 0) {
                        $scentGroupId = $scentGroupItem['id'];
                        $strength = $scentGroupItem['strength'] ?? 50; // Use default if strength is missing
                        $scentGroupSyncData[$scentGroupId] = ['strength' => $strength];
                    } else {
                        \Log::warning('Scent group item without valid ID encountered during sync preparation.', ['item' => $scentGroupItem]);
                    }
                }
                $product->scentGroups()->sync($scentGroupSyncData);
            } else {
                $product->scentGroups()->detach(); // If no scent groups, remove all existing
            }
            // --- End Handle Scent Groups ---

            // --- Handle Usage Profile (Update) ---
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
                // If usage_profile is not sent or is null, delete it.
                $product->usageProfile()->delete();
            }
            // --- End Handle Usage Profile ---

            DB::commit();

            // Reload the product with all necessary relations for the detailed response
            $product->load([
                'images', // The 'images' relationship will automatically order by 'order' if defined in Product model
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
