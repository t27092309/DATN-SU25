<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    // GET // http://localhost:8000/api/cart-items
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.product', 'items.variant')
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
            return [
               'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'slug' => $item->product->slug,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'thumbnail_url' => $item->product->thumbnail_url,
                'variant' => $item->variant ? [
                    'id' => $item->variant->id,
                    'name' => $item->variant->name,
                    'sku' => $item->variant->sku,
                    'price_difference' => $item->variant->price_difference ?? 0,
                ] : null,
            ];
        });

        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'cart_id' => $cart->id,
            'total_items' => $cart->items->sum('quantity'),
            'subtotal' => $subtotal,
            'items' => $items,
        ]);

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
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
        );

        $cartItem = $cart->items()
        ->where('product_id', $request->product_id)
        ->where('product_variant_id', $request->product_variant_id)
        ->first();

        $price = $request->product_variant_id
        ? ProductVariant::find($request->product_variant_id)->price
        : Product::find($request->product_id)->price;

    if ($cartItem) {
        $cartItem->quantity += $request->quantity;
        $cartItem->save();
    } else {
        $cart->items()->create([
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
            'quantity' => $request->quantity,
            'price' => $price
        ]);
    }

    return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công!']);
    }

    // GET // http://localhost:8000/api/cart-items/{id}
    public function show(Request $request, string $id)
    {
        $cartItem = CartItem::with('product', 'variant', 'cart')->find($id);
        if(!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message'=> 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
        }
         return response()->json($cartItem);
    }

    // PUT // http://localhost:8000/api/cart-items/{id}

    public function update(\App\Http\Requests\Client\CartItemRequest $request, string $id)
    {
        $cartItem = CartItem::with('cart')->find($id);
    if (!$cartItem || !$cartItem->cart || $cartItem->cart->user_id !== $request->user()->id) {
        return response()->json(['message'=> 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
    }
    $data = $request->only(['quantity', 'product_variant_id']);
    if(isset($data['quantity'])) {
        $cartItem->quantity = $data['quantity'];
    }
    if(array_key_exists('product_variant_id', $data)) {
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
        return response()->json(['message'=> 'không tìm thấy sản phẩm trong giỏ hàng!'], 404);
    }
    $cartItem->delete();
    return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công!']);
    }
}
