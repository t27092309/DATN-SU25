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

    /**
     * Store a newly created resource in storage.
     * POST /api/admin/products
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Main product image
            // --- Cập nhật: Validation cho ảnh thư viện (gallery images) ---
            'gallery_images' => 'nullable|array', // Allows an array of files
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4096', // Each file in the array
            // -------------------------------------------------------------
            'has_variants' => 'required|boolean',
        ];

        // Thêm quy tắc validation tùy thuộc vào loại sản phẩm (có/không biến thể)
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
            // Thực hiện Validation
            $validatedData = $request->validate($rules, [
                'name.required' => 'Tên sản phẩm là bắt buộc.',
                'name.unique' => 'Tên sản phẩm này đã tồn tại.',
                'category_id.required' => 'Danh mục là bắt buộc.',
                'category_id.exists' => 'Danh mục không hợp lệ.',
                'brand_id.required' => 'Thương hiệu là bắt buộc.',
                'brand_id.exists' => 'Thương hiệu không hợp lệ.',
                'gender.required' => 'Giới tính là bắt buộc.',
                'gender.in' => 'Giới tính không hợp lệ.',
                'image.image' => 'Tệp ảnh chính phải là hình ảnh.',
                'image.mimes' => 'Định dạng ảnh chính không hợp lệ. Chỉ chấp nhận: jpeg, png, jpg, gif, svg.',
                'image.max' => 'Kích thước ảnh chính không được vượt quá 4MB.',
                'gallery_images.array' => 'Thư viện ảnh phải là một mảng.',
                'gallery_images.*.image' => 'Mỗi tệp trong thư viện ảnh phải là một hình ảnh.',
                'gallery_images.*.mimes' => 'Mỗi tệp trong thư viện ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
                'gallery_images.*.max' => 'Mỗi tệp trong thư viện ảnh không được lớn hơn 2MB.',
                'has_variants.required' => 'Loại sản phẩm là bắt buộc.',
                'has_variants.boolean' => 'Loại sản phẩm không hợp lệ.',
                'price.required' => 'Giá sản phẩm là bắt buộc.',
                'price.numeric' => 'Giá sản phẩm phải là số.',
                'price.min' => 'Giá sản phẩm không được âm.',
                'stock.required' => 'Tồn kho là bắt buộc.',
                'stock.integer' => 'Tồn kho phải là số nguyên.',
                'stock.min' => 'Tồn kho không được âm.',

                // Validation cho biến thể
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
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi validation khi thêm sản phẩm.',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        $uploadedFilePaths = []; // Mảng để theo dõi tất cả các đường dẫn file đã tải lên

        try {
            // Xử lý ảnh chính
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products/main_images', 'public');
                $uploadedFilePaths[] = $imagePath; // Thêm vào danh sách để cleanup nếu lỗi
            }

            // Tạo dữ liệu sản phẩm
            $productData = [
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']), // Tự động tạo slug từ tên sản phẩm
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

            // --- Xử lý Thư viện ảnh (Gallery Images) ---
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $galleryImagePath = $file->store('products/gallery_images', 'public');
                    $uploadedFilePaths[] = $galleryImagePath; // Thêm vào danh sách
                    $product->images()->create([
                        'image_url' => $galleryImagePath, // Sử dụng 'image_path' thay vì 'image_url' nếu cột là 'image_path'
                    ]);
                }
            }
            // ------------------------------------------

            // Xử lý biến thể
            if ($validatedData['has_variants'] && isset($validatedData['variants'])) {
                foreach ($validatedData['variants'] as $variantData) {
                    $variantImagePath = null;
                    if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantImagePath = $variantData['image']->store('product_variants/images', 'public'); // Thư mục rõ ràng hơn
                        $uploadedFilePaths[] = $variantImagePath; // Thêm vào danh sách
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

                    // Sử dụng sync để đảm bảo mối quan hệ many-to-many được cập nhật đúng
                    $variant->attributeValues()->sync($variantData['attribute_values']);
                }
            }

            DB::commit();
            // Tải các mối quan hệ để trả về dữ liệu đầy đủ
            $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute']);

            return response()->json([
                'message' => 'Sản phẩm đã được thêm thành công!',
                'data' => new ProductDetailResource($product) // Đảm bảo ProductDetailResource được import
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // Xóa tất cả các file đã tải lên nếu có lỗi xảy ra
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
        // Quy tắc validation cơ bản
        $rules = [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string',
            'gender' => 'sometimes|in:male,female,unisex',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Ảnh chính mới
            'remove_main_image' => 'sometimes|boolean', // Cờ để xóa ảnh chính
            'variants' => 'nullable|json', // Dữ liệu biến thể
        ];

        // Validation cho giá và tồn kho dựa trên `has_variants`
        if (!$product->has_variants) {
            $rules['price'] = 'sometimes|numeric|min:0';
            $rules['stock'] = 'sometimes|integer|min:0';
        }

        $validated = $request->validate($rules, [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'variants.json' => 'Dữ liệu biến thể không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Chuẩn bị dữ liệu cơ bản để cập nhật (loại trừ các trường phức tạp)
            $productDataForUpdate = collect($validated)->except(['variants', 'image', 'remove_main_image'])->toArray();
            if (isset($validated['name'])) {
                $productDataForUpdate['slug'] = Str::slug($validated['name']);
            }

            // 2. Xử lý logic cho ảnh chính (QUAN TRỌNG)
            // Trường hợp 1: Frontend yêu cầu xóa ảnh chính
            if ($request->boolean('remove_main_image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $productDataForUpdate['image'] = null; // Cập nhật DB thành null
            }
            // Trường hợp 2: Frontend tải lên một ảnh chính mới
            elseif ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                // Lưu ảnh mới và lấy đường dẫn
                $path = $request->file('image')->store('products/main_images', 'public');
                $productDataForUpdate['image'] = $path; // Cập nhật DB với đường dẫn mới
            }
            // Trường hợp 3: Không có hành động nào (giữ nguyên ảnh cũ), không cần làm gì cả.

            // 3. Thực hiện cập nhật thông tin sản phẩm
            $product->update($productDataForUpdate);


            // 4. Xử lý các biến thể (logic này đã khá tốt)
            $submittedVariantsData = [];
            if (isset($validated['variants'])) {
                $submittedVariantsData = json_decode($validated['variants'], true);
                if (!is_array($submittedVariantsData)) {
                    throw new \Exception('Dữ liệu biến thể không phải là một mảng hợp lệ.');
                }
            }

            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $variantsToKeepIds = [];

            foreach ($submittedVariantsData as $variantData) {
                $variantRules = [
                    'sku' => ['required', 'string', 'max:255'],
                    'price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'status' => 'required|string',
                    'barcode' => 'nullable|string|max:255',
                    'description' => 'nullable|string',
                    'attribute_value_ids' => 'sometimes|array'
                ];

                // Nếu là biến thể đã tồn tại, nới lỏng quy tắc unique cho SKU
                if (isset($variantData['id']) && $variantData['id']) {
                    $variantRules['sku'][] = Rule::unique('product_variants')->ignore($variantData['id']);
                } else {
                    $variantRules['sku'][] = 'unique:product_variants,sku';
                }

                $variantValidated = validator($variantData, $variantRules)->validate();

                // Tìm và cập nhật hoặc tạo mới biến thể
                $variant = ProductVariant::updateOrCreate(
                    ['id' => $variantData['id'] ?? null, 'product_id' => $product->id],
                    collect($variantValidated)->except('attribute_value_ids')->toArray()
                );

                $variantsToKeepIds[] = $variant->id;

                // Đồng bộ thuộc tính
                if (isset($variantValidated['attribute_value_ids'])) {
                    $variant->attributeValues()->sync($variantValidated['attribute_value_ids']);
                }
            }

            // Xóa các biến thể đã bị loại bỏ ở frontend
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $variantsToKeepIds)
                ->delete(); // Soft delete

            DB::commit();

            // Tải lại tất cả quan hệ để trả về dữ liệu mới nhất
            $product->load(['category', 'brand', 'images', 'variants.attributeValues.attribute']);
            return new ProductDetailResource($product);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi validation khi cập nhật.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật sản phẩm: " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật sản phẩm.', 'error' => $e->getMessage()], 500);
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
