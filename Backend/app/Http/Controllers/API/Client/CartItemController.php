<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CartItemRequest as ClientCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB facade for transactions

class CartItemController extends Controller
{
    // Lấy danh sách sản phẩm trong giỏ hàng
    // GET // http://localhost:8000/api/cart-items
    public function index(Request $request)
    {
        $user = $request->user();

        // Lấy giỏ hàng đang hoạt động và load các quan hệ cần thiết
        $cart = Cart::with([
            'items.variant.product',
            'items.variant.attributeValues.attribute',
            'items.variant.product.variants.attributeValues.attribute',
            // 'items.product.variants.attributeValues.attribute' // This might be redundant if variant is always expected for complex products
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Nếu giỏ hàng chưa tồn tại
        if (!$cart) {
            return response()->json([
                'cart_id' => null,
                'total_items' => 0,
                'subtotal' => 0,
                'items' => [],
            ]);
        }

        // Lấy danh sách item, sắp xếp giảm dần theo ID (mới nhất trước)
        $items = $cart->items->sortByDesc('id')->map(function ($item) {
            $productId = null;
            $productName = 'Sản phẩm không xác định';
            $productSlug = '';
            $productImage = null;
            $displayPrice = (float) $item->price;
            $priceDifference = 0.0;
            $variantData = null;

            if ($item->variant) {
                $variant = $item->variant;
                $product = $variant->product;

                if ($product) {
                    $productId = $product->id;
                    $productName = $product->name;
                    $productSlug = $product->slug;
                    $productImage = $product->image;
                    // Ensure product->price exists before calculation
                    $priceDifference = (float) ($variant->price - ($product->price ?? 0));
                }

                $variantData = [
                    'id' => $variant->id,
                    'name' => $this->getVariantName($variant),
                    'sku' => $variant->sku,
                    'price' => (float) $variant->price, // Current variant price
                    'stock' => $variant->stock, // Include stock for this variant
                    'price_difference' => round($priceDifference, 2),
                    'attributes' => $variant->attributeValues->map(function ($attrValue) {
                        return [
                            'attribute_id' => $attrValue->attribute->id,
                            'attribute_name' => $attrValue->attribute->name,
                            'value_id' => $attrValue->id,
                            'value' => $attrValue->value,
                        ];
                    })->values()->all(),
                ];
            } elseif ($item->product) { // For simple products without variants
                $product = $item->product;
                $productId = $product->id;
                $productName = $product->name;
                $productSlug = $product->slug;
                $productImage = $product->image;
            }

            $availableVariants = [];
            $currentProduct = null;

            if ($item->variant && $item->variant->product) {
                $currentProduct = $item->variant->product;
            } elseif ($item->product) {
                $currentProduct = $item->product;
            }

            if ($currentProduct && $currentProduct->variants->isNotEmpty()) {
                $availableVariants = $currentProduct->variants->map(function ($variant) use ($currentProduct) {
                    // Calculate price difference for each available variant relative to the base product price
                    $variantPriceDifference = (float) ($variant->price - ($currentProduct->price ?? 0));
                    return [
                        'id' => $variant->id,
                        'name' => $this->getVariantName($variant),
                        'sku' => $variant->sku,
                        'price' => (float) $variant->price,
                        'stock' => $variant->stock,
                        'price_difference' => round($variantPriceDifference, 2),
                        'attributes' => $variant->attributeValues->map(function ($attrValue) {
                            return [
                                'attribute_id' => $attrValue->attribute->id,
                                'attribute_name' => $attrValue->attribute->name,
                                'value_id' => $attrValue->id,
                                'value' => $attrValue->value,
                            ];
                        })->values()->all(),
                    ];
                })->all();
            }

            return [
                'id' => $item->id,
                'product_id' => $productId,
                'product_name' => $productName,
                'slug' => $productSlug,
                'price' => round($displayPrice, 2),
                'quantity' => $item->quantity,
                'thumbnail_url' => $productImage,
                'variant' => $variantData,
                'available_variants' => $availableVariants,
            ];
        });

        // Tính tổng tiền
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        return response()->json([
            'cart_id' => $cart->id,
            'total_items' => $cart->items->sum('quantity'),
            'subtotal' => round($subtotal, 2),
            'items' => $items->values(),
        ]);
    }


    // Helper để hiển thị tên biến thể
    protected function getVariantName($variant)
    {
        // Kiểm tra nếu variant hoặc product không tồn tại thì trả về SKU hoặc chuỗi rỗng
        if (!$variant || !$variant->product) {
            return $variant->sku ?? '';
        }

        if ($variant->attributeValues->isEmpty()) {
            return $variant->sku;
        }

        $attributeNames = $variant->attributeValues->map(function ($attrValue) {
            return $attrValue->attribute->name . ': ' . $attrValue->value;
        })->implode(', ');

        return $variant->product->name . ' (' . $attributeNames . ')';
    }

    // Thêm sản phẩm vào giỏ hàng
    // POST // http://localhost:8000/api/cart-items
    public function store(ClientCartItemRequest $request)
    {
        $user = $request->user();

        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'status' => 'active']
            );

            $quantity = $request->quantity;
            $productVariantId = $request->product_variant_id;
            $productId = $request->product_id;

            if ($productVariantId) {
                // Lấy biến thể với lock để tránh race condition
                $variant = ProductVariant::with('product')->lockForUpdate()->find($productVariantId);
                if (!$variant) {
                    DB::rollBack();
                    return response()->json(['message' => 'Biến thể không tồn tại'], 404);
                }

                $cartItem = $cart->items()
                    ->where('product_variant_id', $productVariantId)
                    ->first();

                if ($cartItem) {
                    $newQty = $cartItem->quantity + $quantity;
                    if ($newQty > $variant->stock) {
                        DB::rollBack();
                        return response()->json(['message' => 'Vượt quá tồn kho. Số lượng hiện tại: ' . $cartItem->quantity . ', Tồn kho: ' . $variant->stock], 400);
                    }
                    // Sử dụng increment để cập nhật số lượng một cách an toàn
                    $cartItem->increment('quantity', $quantity);
                } else {
                    if ($variant->stock < $quantity) {
                        DB::rollBack();
                        return response()->json(['message' => 'Số lượng vượt quá tồn kho'], 400);
                    }
                    $cart->items()->create([
                        'product_variant_id' => $productVariantId,
                        'quantity' => $quantity,
                        'price' => $variant->price,
                    ]);
                }
            } else { // Handle simple products without variants
                $product = Product::find($productId);
                if (!$product) {
                    DB::rollBack();
                    return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
                }

                if ($product->variants()->exists()) {
                    DB::rollBack();
                    return response()->json(['message' => 'Sản phẩm yêu cầu chọn biến thể'], 400);
                }

                // For simple products, assume product has a stock field or handle appropriately
                // Example: If product has a 'stock' field and you want to check it
                // if ($product->stock < $quantity) {
                //     DB::rollBack();
                //     return response()->json(['message' => 'Số lượng vượt quá tồn kho cho sản phẩm này'], 400);
                // }

                $cartItem = $cart->items()
                    ->whereNull('product_variant_id') // Ensure we're targeting the base product
                    ->where('product_id', $productId)
                    ->first();

                if ($cartItem) {
                    // Using increment for atomic update
                    $cartItem->increment('quantity', $quantity);
                } else {
                    $cart->items()->create([
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product->price,
                    ]);
                }
            }

            DB::commit(); // Commit transaction
            return $this->returnCartSummary($cart, 'Đã thêm vào giỏ hàng thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json(['message' => 'Có lỗi khi thêm sản phẩm vào giỏ hàng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Cập nhật giỏ hàng
    // PUT // http://localhost:8000/api/cart-items/{id}
    public function update(ClientCartItemRequest $request, string $id)
    {
        $user = $request->user();
        $data = $request->only(['quantity', 'product_variant_id']);

        DB::beginTransaction(); // Start transaction
        try {
            // Lấy cart item hiện tại với lock để tránh race condition
            $cartItem = CartItem::with(['cart', 'variant.product'])
                ->where('id', $id)
                ->lockForUpdate() // Lock the specific cart item
                ->first();

            if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $user->id) {
                DB::rollBack();
                return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng hoặc không có quyền truy cập!'], 404);
            }

            $currentCart = $cartItem->cart; // Store cart reference

            // --- Xử lý thay đổi biến thể ---
            if (array_key_exists('product_variant_id', $data) && $data['product_variant_id'] !== $cartItem->product_variant_id) {
                $newVariantId = $data['product_variant_id'];

                if ($newVariantId === null) {
                    // Logic for changing to a simple product (without variant)
                    // This scenario needs careful consideration if products with variants cannot be simple.
                    // For now, let's assume it means removing variant association.
                    // You might want to prevent this if all products require a variant.
                    if ($cartItem->product && $cartItem->product->variants()->exists()) {
                        DB::rollBack();
                        return response()->json(['message' => 'Không thể chuyển đổi sản phẩm có biến thể thành sản phẩm không có biến thể.'], 400);
                    }
                    $cartItem->product_variant_id = null;
                    $cartItem->price = $cartItem->product->price ?? $cartItem->price; // Set to base product price or keep current
                    $cartItem->product_id = $cartItem->product_id ?? ($cartItem->variant->product_id ?? null); // Ensure product_id is set
                } else {
                    // Lấy biến thể mới với lock
                    $newVariant = ProductVariant::with('product')->lockForUpdate()->find($newVariantId);
                    if (!$newVariant) {
                        DB::rollBack();
                        return response()->json(['message' => 'Biến thể mới không tồn tại'], 404);
                    }

                    // Kiểm tra xem biến thể mới có thuộc cùng sản phẩm gốc không
                    if ($cartItem->variant && $newVariant->product_id !== $cartItem->variant->product_id) {
                        DB::rollBack();
                        return response()->json(['message' => 'Biến thể mới phải thuộc cùng sản phẩm gốc.'], 400);
                    }

                    // Kiểm tra xem biến thể mới đã có trong giỏ hàng chưa (trừ chính cart item đang cập nhật)
                    $existingCartItemWithNewVariant = $currentCart->items()
                        ->where('product_variant_id', $newVariantId)
                        ->where('id', '!=', $cartItem->id) // Exclude the current item being updated
                        ->first();

                    if ($existingCartItemWithNewVariant) {
                        // GỘP BIẾN THỂ: Nếu biến thể mới đã có, gộp số lượng và xóa cart item cũ
                        $totalQuantity = $existingCartItemWithNewVariant->quantity + $cartItem->quantity;

                        if ($totalQuantity > $newVariant->stock) {
                            DB::rollBack();
                            return response()->json(['message' => 'Số lượng gộp vượt quá tồn kho của biến thể mới. Tồn kho: ' . $newVariant->stock], 400);
                        }

                        // Cập nhật số lượng của cart item đã tồn tại
                        $existingCartItemWithNewVariant->quantity = $totalQuantity;
                        $existingCartItemWithNewVariant->price = $newVariant->price; // Update price in case it changed
                        $existingCartItemWithNewVariant->save();

                        // Xóa cart item cũ
                        $cartItem->delete();

                        DB::commit();
                        return $this->returnCartSummary($currentCart, 'Đã gộp sản phẩm trong giỏ hàng thành công!');
                    } else {
                        // CHUYỂN BIẾN THỂ: Nếu biến thể mới chưa có, chỉ cập nhật biến thể cho cart item hiện tại
                        if (($data['quantity'] ?? $cartItem->quantity) > $newVariant->stock) { // Check stock with current or new quantity
                            DB::rollBack();
                            return response()->json(['message' => 'Số lượng vượt quá tồn kho của biến thể mới. Tồn kho: ' . $newVariant->stock], 400);
                        }

                        $cartItem->product_variant_id = $newVariant->id;
                        $cartItem->price = $newVariant->price;
                        // Ensure product_id is set to the product of the new variant
                        $cartItem->product_id = $newVariant->product_id;
                    }
                }
            }

            // --- Xử lý thay đổi số lượng (sau khi xử lý biến thể) ---
            if (isset($data['quantity'])) {
                $targetProduct = $cartItem->variant ?? $cartItem->product; // Get the correct product/variant for stock check
                $stock = $targetProduct ? $targetProduct->stock : null;

                if ($stock !== null && $data['quantity'] > $stock) {
                    DB::rollBack();
                    return response()->json(['message' => 'Số lượng vượt quá tồn kho. Tồn kho: ' . $stock], 400);
                }
                $cartItem->quantity = $data['quantity']; // Assign directly, save() will handle later
            }

            $cartItem->save(); // Save changes to the cart item

            DB::commit(); // Commit transaction
            return $this->returnCartSummary($currentCart, 'Cập nhật sản phẩm thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json(['message' => 'Có lỗi khi cập nhật giỏ hàng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Xóa khỏi giỏ hàng
    // DELETE // http://localhost:8000/api/cart-items/{id}
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        DB::beginTransaction(); // Start transaction
        try {
            $cartItem = CartItem::with('cart')
                ->where('id', $id)
                ->lockForUpdate() // Lock the specific cart item
                ->first();

            if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $user->id) {
                DB::rollBack();
                return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng hoặc không có quyền truy cập!'], 404);
            }

            $cart = $cartItem->cart;
            $cartItem->delete();

            DB::commit(); // Commit transaction
            return $this->returnCartSummary($cart, 'Xóa sản phẩm khỏi giỏ hàng thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json(['message' => 'Có lỗi khi xóa sản phẩm khỏi giỏ hàng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Trả về tổng giỏ hàng sau thao tác
    protected function returnCartSummary($cart, $message)
    {
        // Reload the cart items to ensure latest data after operations
        $cart->load('items');
        $totalItems = $cart->items->sum('quantity');
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        return response()->json([
            'message' => $message,
            'cart_total_items' => $totalItems,
            'cart_subtotal' => round($subtotal, 2),
        ]);
    }
    public function clearSelected(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:cart_items,id', // Đảm bảo các ID tồn tại
        ]);

        $user = $request->user();
        $cartItemIds = $request->input('cart_item_ids');

        try {
            // Xóa các mục giỏ hàng thuộc về người dùng hiện tại và có trong danh sách ID cung cấp
            $deletedCount = CartItem::where('user_id', $user->id)
                ->whereIn('id', $cartItemIds)
                ->delete();

            if ($deletedCount > 0) {
                return response()->json(['message' => 'Các mục giỏ hàng đã chọn đã được xóa thành công.', 'deleted_count' => $deletedCount], 200);
            } else {
                return response()->json(['message' => 'Không có mục giỏ hàng nào được tìm thấy hoặc được xóa.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi xóa các mục giỏ hàng: ' . $e->getMessage()], 500);
        }
    }
}
