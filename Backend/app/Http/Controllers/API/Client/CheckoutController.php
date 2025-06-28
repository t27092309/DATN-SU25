<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
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

        // Lấy địa chỉ giao hàng
        $addressId = $request->input('address_id');

        $address = null;
        if ($addressId) {
            $address = UserAddress::where('id', $addressId)->where('user_id', $user->id)->first();
        }

        if (!$address) {
            // Nếu không truyền ID hoặc không tìm thấy thì lấy mặc định
            $address = UserAddress::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();
        }

        if (!$address) {
            return response()->json([
                'message' => 'Bạn cần cung cấp địa chỉ giao hàng hợp lệ hoặc thiết lập địa chỉ mặc định.'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            // Kiểm tra tồn kho và tính tổng
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                if (!$variant || $variant->stock < $item->quantity) {
                    return response()->json([
                        'message' => 'Sản phẩm "' . ($variant->sku ?? 'không xác định') . '" không đủ tồn kho.'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $total += $variant->price * $item->quantity;
            }

            // Tạo đơn hàng ban đầu
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending',
                'notes' => $request->input('notes'), // nếu có
                'coupon_id' => null,
            ]);

            // Áp dụng mã giảm giá nếu có
            $couponCode = $request->input('coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->where('expires_at', '>=', now())->first();
                if (!$coupon) {
                    return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
                }

                $discountAmount = ($coupon->discount_type === 'percent')
                    ? $total * ($coupon->discount_value / 100)
                    : $coupon->discount_value;

                $total = max(0, $total - $discountAmount); // không âm

                $order->update([
                    'coupon_id' => $coupon->id,
                    'total_price' => $total,
                ]);
            }

            // Tạo các order item
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

                // Trừ kho
                $variant->decrement('stock', $item->quantity);
                $variant->increment('sold', $item->quantity);
            }

            // Cập nhật giỏ hàng
            $cart->update(['status' => 'converted']);

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có lỗi khi đặt hàng.',
                'error' => $e->getMessage()
            ], 500);
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

    // Trừ tồn kho
    $variant->decrement('stock', $validated['quantity']);
    $variant->increment('sold', $validated['quantity']);

    return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id]);
}
}
