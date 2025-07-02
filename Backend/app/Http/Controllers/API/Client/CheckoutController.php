<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request)
{
    $user = $request->user();

    $cart = Cart::with('items.variant.product')->where('user_id', $user->id)->where('status', 'active')->first();
    if (!$cart || $cart->items->isEmpty()) {
        return response()->json(['message' => 'Giỏ hàng rỗng.'], Response::HTTP_BAD_REQUEST);
    }

    $request->validate([
        'address_id' => 'nullable|exists:user_addresses,id',
        'recipient_name' => 'nullable|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'address_line' => 'nullable|string|max:255',
        'ward' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:100',
    ]);

    // Lấy địa chỉ giao hàng
    $address = null;
    if ($request->filled('address_id')) {
        $address = UserAddress::where('id', $request->address_id)->where('user_id', $user->id)->first();
    } elseif ($request->filled(['recipient_name', 'phone_number', 'address_line', 'ward', 'district', 'province'])) {
        $address = (object) $request->only(['recipient_name', 'phone_number', 'address_line', 'ward', 'district', 'province']);
    } else {
        $address = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
    }

    if (!$address) {
        return response()->json(['message' => 'Bạn cần cung cấp địa chỉ giao hàng hợp lệ.'], 400);
    }

    DB::beginTransaction();

    try {
        $total = 0;
        foreach ($cart->items as $item) {
            $variant = $item->variant;
            if (!$variant || $variant->stock < $item->quantity) {
                return response()->json(['message' => 'Sản phẩm "' . ($variant->sku ?? 'không xác định') . '" không đủ tồn kho.'], 400);
            }
            $total += $variant->price * $item->quantity;
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
            'status' => 'pending',
            'notes' => $request->input('notes'),
            'coupon_id' => null,
        ]);

        // Mã giảm giá
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('expires_at', '>=', now())->first();
            if (!$coupon) {
                return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
            }
            $discount = $coupon->discount_type === 'percent' ? ($total * $coupon->discount_value / 100) : $coupon->discount_value;
            $order->update([
                'coupon_id' => $coupon->id,
                'total_price' => max(0, $total - $discount),
            ]);
        }

        // Lưu order_address
        OrderAddress::create([
            'order_id' => $order->id,
            'recipient_name' => $address->recipient_name,
            'phone_number' => $address->phone_number,
            'address_line' => $address->address_line,
            'ward' => $address->ward,
            'district' => $address->district,
            'province' => $address->province,
        ]);

        // Order items
        foreach ($cart->items as $item) {
            $variant = $item->variant;
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'quantity' => $item->quantity,
                'price_each' => $variant->price,
                'variant_sku' => $variant->sku,
                'variant_name' => $variant->product->name,
                'variant_status' => $variant->status,
                'variant_description' => $variant->description,
            ]);
            $variant->decrement('stock', $item->quantity);
            $variant->increment('sold', $item->quantity);
        }

        $cart->update(['status' => 'converted']);

        DB::commit();

        return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Có lỗi khi đặt hàng.', 'error' => $e->getMessage()], 500);
    }
}


    public function buyNow(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'product_variant_id' => 'required|exists:product_variants,id',
        'quantity' => 'required|integer|min:1',
        'address_id' => 'nullable|exists:user_addresses,id',
        'coupon_code' => 'nullable|string',
        'notes' => 'nullable|string'
    ]);

    // Xử lý địa chỉ
    $address = null;
    if (!empty($validated['address_id'])) {
        $address = UserAddress::where('id', $validated['address_id'])->where('user_id', $user->id)->first();
    } else {
        $address = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
    }

    if (!$address) {
        return response()->json(['message' => 'Không tìm thấy địa chỉ giao hàng hợp lệ.'], 400);
    }

    $variant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

    if ($variant->stock < $validated['quantity']) {
        return response()->json(['message' => 'Sản phẩm không đủ tồn kho.'], 400);
    }

    $total = $variant->price * $validated['quantity'];

    // Xử lý coupon nếu có
    $coupon = null;
    if (!empty($validated['coupon_code'])) {
        $coupon = Coupon::where('code', $validated['coupon_code'])
            ->where('expires_at', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
        }

        $discount = $coupon->discount_type === 'percent'
            ? ($total * $coupon->discount_value / 100)
            : $coupon->discount_value;

        $total = max(0, $total - $discount);
    }

    // Tạo đơn hàng
    $order = Order::create([
        'user_id' => $user->id,
        'total_price' => $total,
        'status' => 'pending',
        'notes' => $validated['notes'] ?? null,
        'coupon_id' => $coupon?->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => $validated['quantity'],
        'price_each' => $variant->price,
        'variant_sku' => $variant->sku,
        'variant_name' => $variant->product->name,
        'variant_status' => $variant->status,
        'variant_description' => $variant->description,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    OrderAddress::create([
    'order_id' => $order->id,
    'recipient_name' => $address->recipient_name,
    'phone_number' => $address->phone_number,
    'address_line' => $address->address_line,
    'ward' => $address->ward,
    'district' => $address->district,
    'province' => $address->province,
]);


    // Trừ tồn kho
    $variant->decrement('stock', $validated['quantity']);
    $variant->increment('sold', $validated['quantity']);

    return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id]);
}
}
