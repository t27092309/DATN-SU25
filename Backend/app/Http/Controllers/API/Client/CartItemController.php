<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartItemController extends Controller
{
    // GET // http://localhost:8000/api/cart-items
    public function index(Request $request)
    {
        $user = $request->user();

        // Eager load các mối quan hệ cần thiết:
        // - items.variant.product: Để lấy thông tin sản phẩm gốc từ biến thể.
        // - items.variant.attributeValues.attribute: Để lấy chi tiết thuộc tính (tên thuộc tính và giá trị).
        $cart = Cart::with([
            'items.variant.product',
            'items.variant.attributeValues.attribute'
        ])
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->first();

        if (!$cart) {
            return response()->json([
                'cart_id' => null,
                'total_items' => 0,
                'subtotal' => 0,
                'items' => [],
            ]);
        }

        $items = $cart->items->map(function ($item) {
            $productId = null;
            $productName = 'Sản phẩm không xác định';
            $productSlug = 'san-pham-khong-xac-dinh';
            $productImage = null;
            $displayPrice = 0;
            $variantData = null;

            if ($item->variant) {
                $variant = $item->variant;
                $product = $variant->product; // Lấy sản phẩm gốc thông qua biến thể

                if ($product) {
                    $productId = $product->id;
                    $productName = $product->name;
                    $productSlug = $product->slug;
                    $productImage = $product->image; // Tên cột là 'image' trong bảng products
                }

                $displayPrice = $variant->price; // Lấy giá từ biến thể (giá hiện tại của biến thể)

                $variantData = [
                    'id' => $variant->id,
                    // Tên biến thể có thể được tạo từ các thuộc tính hoặc dùng SKU
                    'name' => $this->getVariantName($variant), // Sử dụng hàm trợ giúp để tạo tên biến thể
                    'sku' => $variant->sku,
                    'price_difference' => 0, // Tính toán nếu cần
                    'attributes' => $variant->attributeValues->map(function($attrValue) {
                        return [
                            'attribute_id' => $attrValue->attribute->id,
                            'attribute_name' => $attrValue->attribute->name, // Tên thuộc tính (e.g., Color, Size)
                            'value_id' => $attrValue->id,
                            'value' => $attrValue->value, // Giá trị thuộc tính (e.g., Red, M)
                        ];
                    })->values()->all(), // Đảm bảo trả về mảng tuần tự
                ];
            } else {
                // Trường hợp cart_item không có variant_id (nếu bạn có logic này)
                // Theo logic trước đó, mọi cart_item đều phải có variant_id.
                // Nếu không có variant, bạn có thể muốn trả về lỗi hoặc bỏ qua item này.
                // Để đơn giản, nếu không có variant, chúng ta sẽ trả về thông tin sản phẩm chính (nếu có).
                // NHƯNG, nếu bạn chỉ dựa vào product_variant_id trong cart_items, phần này có thể không bao giờ chạy
                // và sản phẩm không có biến thể cần một biến thể mặc định.
                if ($item->product) { // Điều này chỉ đúng nếu cart_items có mối quan hệ product trực tiếp
                    $product = $item->product;
                    $productId = $product->id;
                    $productName = $product->name;
                    $productSlug = $product->slug;
                    $productImage = $product->image;
                    $displayPrice = $product->price;
                }
            }

            return [
                'id' => $item->id,
                'product_id' => $productId,
                'product_name' => $productName,
                'slug' => $productSlug,
                'price' => $displayPrice, // Giá hiện tại của biến thể/sản phẩm gốc
                'quantity' => $item->quantity,
                'thumbnail_url' => $productImage,
                'variant' => $variantData,
            ];
        });

        // Tính toán subtotal dựa trên giá hiện tại của biến thể/sản phẩm trong giỏ
        $subtotal = $cart->items->sum(function ($item) {
            $itemPrice = 0;
            if ($item->variant) {
                $itemPrice = $item->variant->price;
            }
            // else if ($item->product) { // Chỉ nếu cart_items có product_id trực tiếp
            //     $itemPrice = $item->product->price;
            // }
            return $itemPrice * $item->quantity;
        });

        return response()->json([
            'cart_id' => $cart->id,
            'total_items' => $cart->items->sum('quantity'),
            'subtotal' => $subtotal,
            'items' => $items,
        ]);
    }

    /**
     * Helper function to generate variant name from its attributes.
     * @param \App\Models\ProductVariant $variant
     * @return string
     */
    protected function getVariantName($variant)
    {
        if ($variant->attributeValues->isEmpty()) {
            return $variant->sku; // Fallback to SKU if no attributes
        }

        $attributeNames = $variant->attributeValues->map(function ($attrValue) {
            return $attrValue->attribute->name . ': ' . $attrValue->value;
        })->implode(', ');

        return $variant->product->name . ' (' . $attributeNames . ')';
    }

    // POST // http://localhost:8000/api/cart-items
    // test postman
    // {
    //   "product_id": 1,
    //   "quantity": 1,
    //   "product_variant_id": 1
    // }
    public function store(\App\Http\Requests\Client\CartItemRequest $request)
    {
        $user = $request->user();

        // Tìm hoặc tạo giỏ hàng 'active' cho người dùng hiện tại.
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
        );

        // Lấy product_variant_id từ request
        $productVariantId = $request->product_variant_id;
        $quantity = $request->quantity;

        // Kiểm tra xem product_variant_id có tồn tại không.
        // Đây là bước quan trọng để đảm bảo chỉ thêm các biến thể hợp lệ vào giỏ hàng.
        $variant = ProductVariant::find($productVariantId);

        if (!$variant) {
            return response()->json(
                ['message' => 'Biến thể sản phẩm không hợp lệ hoặc không tìm thấy.'],
                Response::HTTP_NOT_FOUND // Sử dụng mã lỗi 404 Not Found
            );
        }

        // Tìm kiếm cart item hiện có trong giỏ hàng của người dùng với biến thể này.
        $cartItem = $cart->items()
            ->where('product_variant_id', $productVariantId)
            ->first();

        if ($cartItem) {
            // Nếu sản phẩm đã tồn tại trong giỏ, tăng số lượng.
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Nếu sản phẩm chưa có trong giỏ, tạo mới.
            $cart->items()->create([
                'product_variant_id' => $productVariantId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công!']);
    }

    // GET // http://localhost:8000/api/cart-items/{id}
    public function show(Request $request, string $id)
    {
        $cartItem = CartItem::with('product', 'variant', 'cart')->find($id);
        if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }
        return response()->json($cartItem);
    }

    // PUT // http://localhost:8000/api/cart-items/{id}

    public function update(\App\Http\Requests\Client\CartItemRequest $request, string $id)
    {
        $cartItem = CartItem::with('cart')->find($id);
        if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }
        $data = $request->only(['quantity', 'product_variant_id']);
        if (isset($data['quantity'])) {
            $cartItem->quantity = $data['quantity'];
        }
        if (array_key_exists('product_variant_id', $data)) {
            $cartItem->product_variant_id = $data['product_variant_id'];
        }
        $cartItem->save();
        return response()->json(['message' => 'Cập nhật sản phẩm trong giỏ hàng thành công!']);
    }

    

    // DELETE // http://localhost:8000/api/cart-items/{id}
    public function destroy(Request $request, string $id)
    {
        $cartItem = CartItem::with('cart')->find($id);
        if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }
        $cartItem->delete();
        return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công!']);
    }
}
