<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductVariant::with('product')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'sku' => 'required|unique:product_variants,sku',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'status' => 'in:available,out_of_stock,discontinued',
        'barcode' => 'nullable|string',
        'description' => 'nullable|string',
    ], [
        'product_id.required' => 'ID sản phẩm là bắt buộc.',
        'product_id.exists' => 'Sản phẩm không tồn tại.',
        'sku.required' => 'SKU là bắt buộc.',
        'sku.unique' => 'SKU đã tồn tại, vui lòng chọn SKU khác.',
        'price.required' => 'Giá là bắt buộc.',
        'price.numeric' => 'Giá phải là một số.',
        'stock.required' => 'Số lượng tồn kho là bắt buộc.',
        'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
        'status.in' => 'Trạng thái phải là một trong các giá trị: available, out_of_stock, discontinued.',
        'barcode.string' => 'Mã vạch phải là chuỗi ký tự.',
        'description.string' => 'Mô tả phải là chuỗi ký tự.',
    ]);

    $variant = ProductVariant::create($validated);

    return response()->json($variant, 201);
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return ProductVariant::with('product')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $variant = ProductVariant::findOrFail($id);

    $validated = $request->validate([
        'sku' => 'sometimes|unique:product_variants,sku,' . $variant->id,
        'price' => 'sometimes|numeric',
        'stock' => 'sometimes|integer',
        'status' => 'in:available,out_of_stock,discontinued',
        'barcode' => 'nullable|string',
        'description' => 'nullable|string',
    ], [
        'sku.unique' => 'SKU đã tồn tại, vui lòng chọn SKU khác.',
        'price.numeric' => 'Giá phải là một số.',
        'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
        'status.in' => 'Trạng thái phải là một trong các giá trị: available, out_of_stock, discontinued.',
        'barcode.string' => 'Mã vạch phải là chuỗi ký tự.',
        'description.string' => 'Mô tả phải là chuỗi ký tự.',
    ]);

    $variant->update($validated);

    return response()->json($variant);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->delete();

        return response()->json([
            'message' => 'Product variant deleted successfully.'
        ], 200);
    }

    public function restore($id)
    {
        $variant = ProductVariant::withTrashed()->findOrFail($id);
        $variant->restore();

        return response()->json(['message' => 'Product variant restored successfully.']);
    }

    public function trashed()
    {
        $trashed = ProductVariant::onlyTrashed()
            ->with('product')
            ->orderByDesc('id')
            ->get();

        return response()->json($trashed);
    }
}
