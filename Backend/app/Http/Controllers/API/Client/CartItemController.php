<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CartItemRequest as ClientCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

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
        'items.variant.attributeValues.attribute'
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
        $displayPrice = (float) $item->price; // Ép kiểu float
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
                $priceDifference = (float) ($variant->price - $product->price);
            }

            $variantData = [
                'id' => $variant->id,
                'name' => $this->getVariantName($variant),
                'sku' => $variant->sku,
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
        } elseif ($item->product) {
            $product = $item->product;
            $productId = $product->id;
            $productName = $product->name;
            $productSlug = $product->slug;
            $productImage = $product->image;
        }

        return [
            'id' => $item->id,
            'product_id' => $productId,
            'product_name' => $productName,
            'slug' => $productSlug,
            'price' => round($displayPrice, 2), // Ép kiểu float, làm tròn 2 chữ số
            'quantity' => $item->quantity,
            'thumbnail_url' => $productImage,
            'variant' => $variantData,
        ];
    });

    // Tính tổng tiền
    $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

    return response()->json([
        'cart_id' => $cart->id,
        'total_items' => $cart->items->sum('quantity'),
        'subtotal' => round($subtotal, 2), // Làm tròn cho đẹp
        'items' => $items->values(), // Reset lại key sau sort
    ]);
}


    // Helper để hiển thị tên biến thể
    protected function getVariantName($variant)
    {
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
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active']
        );

        $quantity = $request->quantity;
        $productVariantId = $request->product_variant_id;
        $productId = $request->product_id;

        if ($productVariantId) {
            $variant = ProductVariant::with('product')->find($productVariantId);
            if (!$variant) {
                return response()->json(['message' => 'Biến thể không tồn tại'], 404);
            }

            if ($variant->stock < $quantity) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho'], 400);
            }

            $cartItem = $cart->items()
                ->where('product_variant_id', $productVariantId)
                ->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $quantity;
                if ($newQty > $variant->stock) {
                    return response()->json(['message' => 'Vượt quá tồn kho'], 400);
                }
                $cartItem->quantity = $newQty;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity,
                    'price' => $variant->price,
                ]);
            }
        } else {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
            }

            if ($product->variants()->exists()) {
                return response()->json(['message' => 'Sản phẩm yêu cầu chọn biến thể'], 400);
            }

            $cartItem = $cart->items()
                ->whereNull('product_variant_id')
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }
        }

        return $this->returnCartSummary($cart, 'Đã thêm vào giỏ hàng thành công!');
    }

    // Cập nhật giỏ hàng
    // PUT // http://localhost:8000/api/cart-items/{id}
    public function update(ClientCartItemRequest $request, string $id)
    {
        $cartItem = CartItem::with(['cart', 'variant.product'])->find($id);
        if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }

        $data = $request->only(['quantity', 'product_variant_id']);

        if (isset($data['quantity'])) {
            $stock = $cartItem->variant ? $cartItem->variant->stock : null;
            if ($stock !== null && $data['quantity'] > $stock) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho'], 400);
            }

            $cartItem->quantity = $data['quantity'];
        }

        if (array_key_exists('product_variant_id', $data)) {
            if ($data['product_variant_id'] === null) {
                $cartItem->product_variant_id = null;
            } else {
                $newVariant = ProductVariant::with('product')->find($data['product_variant_id']);
                if (!$newVariant) {
                    return response()->json(['message' => 'Biến thể mới không tồn tại'], 404);
                }

                if ($newVariant->stock < $cartItem->quantity) {
                    return response()->json(['message' => 'Số lượng vượt quá tồn kho'], 400);
                }

                $cartItem->product_variant_id = $newVariant->id;
                $cartItem->price = $newVariant->price;
            }
        }

        $cartItem->save();
        return $this->returnCartSummary($cartItem->cart, 'Cập nhật sản phẩm thành công!');
    }

    // Xóa khỏi giỏ hàng
    // DELETE // http://localhost:8000/api/cart-items/{id}
    public function destroy(Request $request, string $id)
    {
        $cartItem = CartItem::with('cart')->find($id);
        if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        return $this->returnCartSummary($cart, 'Xóa sản phẩm khỏi giỏ hàng thành công!');
    }

    // Trả về tổng giỏ hàng sau thao tác
    protected function returnCartSummary($cart, $message)
    {
        $totalItems = $cart->items->sum('quantity');
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        return response()->json([
            'message' => $message,
            'cart_total_items' => $totalItems,
            'cart_subtotal' => $subtotal,
        ]);
    }
}
