<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    private function getStockStatus($quantity)
    {
        if ($quantity === 0) return 'Hết hàng';
        if ($quantity <= 5) return 'Sắp hết hàng';
        return 'Còn hàng';
    }

    public function overview(Request $request)
    {
        $keyword = $request->input('keyword');
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');

        $products = Product::with(['variants', 'category', 'brand'])
            ->when($keyword, fn($q) => $q->where('name', 'like', "%$keyword%"))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->get();

        $result = [];

        foreach ($products as $product) {
            if ($product->variants->isEmpty()) {
                // Sản phẩm không có biến thể
                $inventoryQuantity = InventoryLog::whereNull('product_variant_id')
                    ->whereIn('id', function ($query) use ($product) {
                        $query->select('id')
                            ->from('product_variants')
                            ->where('product_id', $product->id);
                    })
                    ->sum('quantity_change');

                $status = $this->getStockStatus($inventoryQuantity);
                $result[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'type' => 'simple',
                    'sku' => null,
                    'quantity' => $inventoryQuantity,
                    'price' => $product->price,
                    'stock_value' => $product->price * $inventoryQuantity,
                    'status' => $status,
                ];
            } else {
                foreach ($product->variants as $variant) {
                    $quantity = InventoryLog::where('product_variant_id', $variant->id)
                        ->sum('quantity_change');

                    $status = $this->getStockStatus($quantity);
                    $result[] = [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'name' => $product->name,
                        'type' => 'variant',
                        'sku' => $variant->sku,
                        'quantity' => $quantity,
                        'price' => $variant->price,
                        'stock_value' => $variant->price * $quantity,
                        'status' => $status,
                    ];
                }
            }
        }

        return response()->json($result);
    }

    public function adjustStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'product_id' => 'required_without:product_variant_id|exists:products,id',
            'new_quantity' => 'required|integer',
            'note' => 'required|string|min:5',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $variantId = $request->input('product_variant_id');
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');

        // Lấy tồn kho hiện tại
        $currentQuantity = InventoryLog::where('warehouse_id', $warehouseId)
            ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
            ->when(!$variantId, fn($q) => $q->whereNull('product_variant_id')->where('product_id', $productId))
            ->sum('quantity_change');

        $newQuantity = $request->input('new_quantity');
        $difference = $newQuantity - $currentQuantity;

        if ($difference === 0) {
            return response()->json(['message' => 'Số lượng tồn kho không thay đổi.'], 200);
        }

        // Ghi log điều chỉnh
        InventoryLog::create([
            'product_variant_id' => $variantId,
            'product_id' => $variantId ? null : $productId, // nếu không có biến thể
            'warehouse_id' => $warehouseId,
            'quantity_change' => $difference,
            'type' => 'adjustment',
            'note' => $request->input('note'),
        ]);

        return response()->json([
            'message' => 'Điều chỉnh tồn kho thành công.',
            'quantity_changed' => $difference,
            'new_quantity' => $newQuantity,
        ]);
    }
    public function index(Request $request)
{
    $query = InventoryLog::with(['variant.product', 'user'])
        ->when($request->product_variant_id, fn($q) => $q->where('product_variant_id', $request->product_variant_id))
        ->when($request->type, fn($q) => $q->where('type', $request->type))
        ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
        ->orderByDesc('created_at');

    return response()->json($query->paginate(20));
}
}
