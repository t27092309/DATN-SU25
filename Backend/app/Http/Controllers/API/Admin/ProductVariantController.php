<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product; // Cần import Product để kiểm tra và tạo biến thể mặc định
use App\Http\Resources\ProductVariantResource; // <-- Đảm bảo import
use Illuminate\Support\Facades\DB; // Dùng cho transaction
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/admin/product-variants
     */
    public function index()
    {
        // Luôn eager load product và attributeValues cho danh sách
        $variants = ProductVariant::with(['product', 'attributeValues'])
            ->orderByDesc('id')
            ->paginate(15);

        // TRẢ VỀ RESOURCE COLLECTION CHO DANH SÁCH
        return ProductVariantResource::collection($variants);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/admin/product-variants
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => ['required', 'string', Rule::in(['available', 'out_of_stock', 'discontinued'])],
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // Dành cho biến thể có thuộc tính: Mảng các IDs của attribute_values
            'attribute_value_ids' => 'nullable|array',
            'attribute_value_ids.*' => 'exists:attribute_values,id',
        ], [
            'product_id.required' => 'ID sản phẩm là bắt buộc.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'sku.required' => 'SKU là bắt buộc.',
            'sku.unique' => 'SKU đã tồn tại, vui lòng chọn SKU khác.',
            'price.required' => 'Giá là bắt buộc.',
            'price.numeric' => 'Giá phải là một số.',
            'stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là một trong các giá trị: available, out_of_stock, discontinued.',
            'barcode.string' => 'Mã vạch phải là chuỗi ký tự.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'attribute_value_ids.array' => 'Các giá trị thuộc tính phải là một mảng.',
            'attribute_value_ids.*.exists' => 'Một hoặc nhiều giá trị thuộc tính không tồn tại.',
        ]);

        try {
            DB::beginTransaction();

            $variant = ProductVariant::create($validated);

            // Đồng bộ các giá trị thuộc tính cho biến thể
            if (!empty($validated['attribute_value_ids'])) {
                $variant->attributeValues()->sync($validated['attribute_value_ids']);
            }

            DB::commit();

            // Load lại các quan hệ để trả về đầy đủ qua Resource
            $variant->load(['product', 'attributeValues']);

            // TRẢ VỀ RESOURCE ĐƠN LẺ CHO ITEM MỚI TẠO
            return new ProductVariantResource($variant); // Laravel tự động set status 201 Created

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create product variant.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/admin/product-variants/{id}
     */
    public function show(string $id)
    {
        // Luôn eager load product và attributeValues cho hiển thị chi tiết
        $variant = ProductVariant::with(['product', 'attributeValues'])->findOrFail($id);

        // TRẢ VỀ RESOURCE ĐƠN LẺ CHO HIỂN THỊ
        return new ProductVariantResource($variant);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/admin/product-variants/{id}
     */
    public function update(Request $request, string $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $validated = $request->validate([
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('product_variants')->ignore($variant->id)],
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'status' => ['sometimes', 'string', Rule::in(['available', 'out_of_stock', 'discontinued'])],
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // Dành cho biến thể có thuộc tính: Mảng các IDs của attribute_values
            'attribute_value_ids' => 'nullable|array',
            'attribute_value_ids.*' => 'exists:attribute_values,id',
        ], [
            'sku.unique' => 'SKU đã tồn tại, vui lòng chọn SKU khác.',
            'price.numeric' => 'Giá phải là một số.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'status.in' => 'Trạng thái phải là một trong các giá trị: available, out_of_stock, discontinued.',
            'barcode.string' => 'Mã vạch phải là chuỗi ký tự.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'attribute_value_ids.array' => 'Các giá trị thuộc tính phải là một mảng.',
            'attribute_value_ids.*.exists' => 'Một hoặc nhiều giá trị thuộc tính không tồn tại.',
        ]);

        try {
            DB::beginTransaction();

            $variant->update($validated);

            // Đồng bộ các giá trị thuộc tính cho biến thể
            // Nếu 'attribute_value_ids' không được gửi hoặc là mảng rỗng, sẽ ngắt kết nối
            if (isset($validated['attribute_value_ids'])) {
                $variant->attributeValues()->sync($validated['attribute_value_ids']);
            } else {
                // Nếu 'attribute_value_ids' không có trong request, giữ nguyên các quan hệ hiện có
                // Nếu bạn muốn xóa hết quan hệ khi nó không được gửi, dùng $variant->attributeValues()->detach();
            }


            DB::commit();

            // Load lại các quan hệ để trả về đầy đủ qua Resource
            $variant->load(['product', 'attributeValues']);

            // TRẢ VỀ RESOURCE ĐƠN LẺ CHO ITEM ĐÃ CẬP NHẬT
            return new ProductVariantResource($variant);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update product variant.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/admin/product-variants/{id}
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::findOrFail($id);

        try {
            DB::beginTransaction();

            // Xóa các quan hệ trong bảng trung gian (nếu không dùng cascade on delete ở migration)
            $variant->attributeValues()->detach();

            $variant->delete(); // Soft delete

            DB::commit();
            // TRẢ VỀ 204 NO CONTENT CHO XÓA THÀNH CÔNG
            return response()->noContent(); // Mã 204 không trả về body

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete product variant.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore a soft-deleted resource.
     * POST /api/admin/product-variants/{id}/restore
     */
    public function restore($id)
    {
        $variant = ProductVariant::withTrashed()->findOrFail($id);
        $variant->restore();

        $variant->load(['product', 'attributeValues']); // Load lại để trả về resource
        return new ProductVariantResource($variant);
    }

    /**
     * Display a listing of trashed resources.
     * GET /api/admin/product-variants/trashed
     */
    public function trashed()
    {
        $trashed = ProductVariant::onlyTrashed()
            ->with(['product', 'attributeValues'])
            ->orderByDesc('id')
            ->get();

        return ProductVariantResource::collection($trashed);
    }

    /**
     * API để tạo biến thể mặc định cho sản phẩm không có biến thể rõ ràng.
     * Hoặc để tạo biến thể với các giá trị thuộc tính đã cho.
     * POST /api/admin/products/{product}/variants/generate
     */
    public function generateForProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => ['required', 'string', Rule::in(['available', 'out_of_stock', 'discontinued'])],
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // Nếu có biến thể, cần gửi mảng attribute_value_ids
            'attribute_value_ids' => 'nullable|array',
            'attribute_value_ids.*' => 'exists:attribute_values,id',
            // Có thể thêm một cờ để xác định nếu đây là biến thể mặc định
            'is_default' => 'boolean',
        ], [
            'sku.required' => 'SKU là bắt buộc.',
            'sku.unique' => 'SKU đã tồn tại, vui lòng chọn SKU khác.',
            'price.required' => 'Giá là bắt buộc.',
            'price.numeric' => 'Giá phải là một số.',
            'stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là một trong các giá trị: available, out_of_stock, discontinued.',
            'attribute_value_ids.array' => 'Các giá trị thuộc tính phải là một mảng.',
            'attribute_value_ids.*.exists' => 'Một hoặc nhiều giá trị thuộc tính không tồn tại.',
        ]);

        try {
            DB::beginTransaction();

            // Nếu đây là biến thể mặc định, đảm bảo chỉ có một mặc định
            if (isset($validated['is_default']) && $validated['is_default']) {
                $product->variants()->update(['is_default' => false]); // Đặt tất cả các biến thể khác của sản phẩm này về không mặc định
                // Thêm cột `is_default` vào bảng `product_variants` nếu chưa có
            }

            $variant = $product->variants()->create([
                'sku' => $validated['sku'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'status' => $validated['status'],
                'barcode' => $validated['barcode'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
            ]);

            if (!empty($validated['attribute_value_ids'])) {
                $variant->attributeValues()->sync($validated['attribute_value_ids']);
            }

            DB::commit();

            $variant->load(['product', 'attributeValues']);
            return new ProductVariantResource($variant);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to generate product variant.', 'error' => $e->getMessage()], 500);
        }
    }
}