<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function getCheckoutItems(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'integer|exists:cart_items,id', // Ensure each ID exists in cart_items table
        ]);

        $cartItemIds = $request->input('cart_item_ids');

        // Eager load necessary relationships for the frontend display
        $checkoutItems = CartItem::whereIn('id', $cartItemIds)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'active'); // Only consider active carts
            })
            ->with(['variant.product']) // Load product and variant details
            ->get();

        if ($checkoutItems->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm hợp lệ trong giỏ hàng của bạn.'], Response::HTTP_NOT_FOUND);
        }

        $formattedItems = $checkoutItems->map(function ($item) {
            // Perform a pre-check for stock here
            if (!$item->variant || $item->variant->stock < $item->quantity) {
                // If you want to allow checkout with warnings, you can add a 'stock_error' flag
                // Otherwise, you might return an error directly as below.
                return response()->json([
                    'message' => 'Sản phẩm "' . ($item->variant->product->name ?? 'Không xác định') . ' - ' . ($item->variant->name ?? '') . '" không đủ tồn kho (' . $item->variant->stock . ' còn lại).'
                ], Response::HTTP_BAD_REQUEST);
            }

            return [
                'id' => $item->id,
                'product_name' => $item->variant->product->name ?? 'Sản phẩm không rõ',
                'thumbnail_url' => $item->variant->product->thumbnail_url ?? 'https://via.placeholder.com/64', // Provide a fallback
                'price' => $item->variant->price,
                'quantity' => $item->quantity,
                'variant' => [
                    'id' => $item->variant->id,
                    'name' => $item->variant->name,
                    'sku' => $item->variant->sku,
                ],
                // Add any other details needed by the frontend
            ];
        });

        // Check if any stock error occurred during mapping
        foreach ($formattedItems as $item) {
            if ($item instanceof \Illuminate\Http\JsonResponse && $item->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                return $item; // Return the stock error response
            }
        }

        return response()->json(['items' => $formattedItems]);
    }

    public function placeOrder(Request $request)
    {
        $user = $request->user();

        // Eager load các mối quan hệ cần thiết để xây dựng variant_name đầy đủ
        $cart = Cart::with([
            'items.variant' => function ($query) {
                $query->with('product', 'attributeValues.attribute'); // Thêm eager loads
            }
        ])->where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng rỗng.'], Response::HTTP_BAD_REQUEST);
        }

        // Validation ban đầu, các trường địa chỉ vẫn là nullable ở đây để kiểm tra logic
        $validated = $request->validate([
            'address_id' => 'nullable|exists:user_addresses,id',
            'recipient_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500', // Thêm max length cho notes
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|string|in:cod,bank_transfer,e_wallet', // Yêu cầu phương thức thanh toán
        ]);

        // Lấy và xác thực địa chỉ giao hàng chi tiết hơn, đồng bộ với buyNow
        $addressData = [];
        if (!empty($validated['address_id'])) {
            $userAddress = UserAddress::where('id', $validated['address_id'])->where('user_id', $user->id)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Địa chỉ đã chọn không hợp lệ hoặc không thuộc về bạn.'], Response::HTTP_BAD_REQUEST);
            }
            $addressData = $userAddress->toArray();
        } elseif (
            isset($validated['recipient_name']) &&
            isset($validated['phone_number']) &&
            isset($validated['address_line']) &&
            isset($validated['ward']) &&
            isset($validated['district']) &&
            isset($validated['province'])
        ) {
            // Nếu người dùng nhập địa chỉ mới, cần đảm bảo tất cả các trường cần thiết được điền
            $request->validate([ // Validation lại cho các trường bắt buộc của địa chỉ mới
                'recipient_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'address_line' => 'required|string|max:255',
                'ward' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'province' => 'required|string|max:100',
            ]);
            $addressData = $request->only(['recipient_name', 'phone_number', 'address_line', 'ward', 'district', 'province']);
        } else {
            // Nếu không có address_id và không cung cấp đầy đủ thông tin địa chỉ mới,
            // tìm địa chỉ mặc định
            $userAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Bạn cần cung cấp thông tin địa chỉ giao hàng hoặc chọn một địa chỉ có sẵn.'], Response::HTTP_BAD_REQUEST);
            }
            $addressData = $userAddress->toArray();
        }

        DB::beginTransaction();

        try {
            $total = 0;
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                // Nếu variant null hoặc tồn kho không đủ (chú ý: status 'unavailable' cũng nên được check)
                if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                    return response()->json(['message' => 'Sản phẩm "' . ($variant->sku ?? 'không xác định') . '" không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
                }
                $total += $variant->price * $item->quantity;
            }

            // Có thể thêm tính phí vận chuyển ở đây, ví dụ: $shippingFee = calculateShippingFee($addressData);
            // $total += $shippingFee;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => null,
                'payment_method' => $validated['payment_method'], // Lưu phương thức thanh toán
            ]);

            // Mã giảm giá
            $finalTotal = $total;
            if (!empty($validated['coupon_code'])) {
                $coupon = Coupon::where('code', $validated['coupon_code'])
                    ->where('expires_at', '>=', now())
                    // ->where('min_total', '<=', $total) // Thêm điều kiện min_total nếu có
                    // ->where(...) // Thêm các điều kiện khác của coupon (ví dụ: số lần dùng, user cụ thể)
                    ->first();

                if (!$coupon) {
                    return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], Response::HTTP_BAD_REQUEST);
                }

                $discount = $coupon->discount_type === 'percent' ? ($total * $coupon->discount_value / 100) : $coupon->discount_value;
                $finalTotal = max(0, $total - $discount);

                $order->update([
                    'coupon_id' => $coupon->id,
                    'total_price' => $finalTotal,
                ]);

                // Cập nhật trạng thái coupon nếu là mã dùng một lần hoặc có giới hạn số lần dùng
                // $coupon->increment('used_count');
            }

            // Cập nhật tổng tiền cuối cùng nếu có giảm giá
            $order->update(['total_price' => $finalTotal]);

            // Lưu order_address
            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            // Order items
            foreach ($cart->items as $item) {
                $variant = $item->variant;

                // Xây dựng variant_name đầy đủ từ product và attribute values
                $variantName = $variant->product->name; // Tên sản phẩm chính
                $attributeParts = [];
                if ($variant->relationLoaded('attributeValues')) { // Kiểm tra xem relation đã được load chưa
                    foreach ($variant->attributeValues as $attrValue) {
                        if ($attrValue->relationLoaded('attribute') && $attrValue->attribute && $attrValue->value) {
                            $attributeParts[] = $attrValue->attribute->name . ': ' . $attrValue->value;
                        }
                    }
                }
                if (!empty($attributeParts)) {
                    $variantName .= ' (' . implode(' / ', $attributeParts) . ')';
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item->quantity,
                    'price_each' => $variant->price,
                    'variant_sku' => $variant->sku,
                    'variant_name' => $variantName, // <-- Đã sửa để lưu tên biến thể đầy đủ
                    'variant_status' => $variant->status,
                    'variant_description' => $variant->description,
                ]);
                $variant->decrement('stock', $item->quantity);
                $variant->increment('sold', $item->quantity);
            }

            // Cập nhật trạng thái giỏ hàng sau khi đặt thành công
            $cart->update(['status' => 'converted']);

            DB::commit();

            return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng từ giỏ: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Có lỗi khi đặt hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function buyNow(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'address_id' => 'nullable|exists:user_addresses,id',
            'recipient_name' => 'nullable|string|max:255', // Thêm validation cho địa chỉ mới
            'phone_number' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_method' => 'required|string|in:cod,bank_transfer,e_wallet', // Thêm phương thức thanh toán
        ]);

        // Lấy địa chỉ giao hàng
        $addressData = [];
        if (!empty($validated['address_id'])) {
            $userAddress = UserAddress::where('id', $validated['address_id'])->where('user_id', $user->id)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Địa chỉ đã chọn không hợp lệ hoặc không thuộc về bạn.'], Response::HTTP_BAD_REQUEST);
            }
            $addressData = $userAddress->toArray();
        } elseif (isset($validated['recipient_name']) && isset($validated['phone_number']) && isset($validated['address_line']) && isset($validated['ward']) && isset($validated['district']) && isset($validated['province'])) {
            // Nếu người dùng nhập địa chỉ mới, cần đảm bảo tất cả các trường cần thiết được điền
            $request->validate([
                'recipient_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'address_line' => 'required|string|max:255',
                'ward' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'province' => 'required|string|max:100',
            ]);
            $addressData = $request->only(['recipient_name', 'phone_number', 'address_line', 'ward', 'district', 'province']);
        } else {
            $userAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Bạn cần cung cấp thông tin địa chỉ giao hàng hoặc chọn một địa chỉ có sẵn.'], Response::HTTP_BAD_REQUEST);
            }
            $addressData = $userAddress->toArray();
        }

        $variant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

        if ($variant->stock < $validated['quantity'] || $variant->status === 'unavailable') {
            return response()->json(['message' => 'Sản phẩm không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
        }

        $total = $variant->price * $validated['quantity'];

        // Xử lý coupon nếu có
        $coupon = null;
        $finalTotal = $total;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where('expires_at', '>=', now())
                ->first();

            if (!$coupon) {
                return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], Response::HTTP_BAD_REQUEST);
            }

            $discount = $coupon->discount_type === 'percent'
                ? ($total * $coupon->discount_value / 100)
                : $coupon->discount_value;

            $finalTotal = max(0, $total - $discount);
        }

        DB::beginTransaction();
        try {
            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'payment_method' => $validated['payment_method'], // Lưu phương thức thanh toán
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
            ]);

            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            // Trừ tồn kho và tăng số lượng đã bán
            $variant->decrement('stock', $validated['quantity']);
            $variant->increment('sold', $validated['quantity']);

            DB::commit();
            return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi khi mua ngay: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Có lỗi xảy ra khi mua hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAvailableCoupons(Request $request)
    {
        // Get the current date and time
        $now = Carbon::now();

        // Query coupons that are currently active and not expired
        $availableCoupons = Coupon::where(function ($query) use ($now) {
            $query->whereNull('start_date') // Coupon has no start date (always active)
                ->orWhere('start_date', '<=', $now->toDateString()); // Or start date is in the past
        })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date') // Coupon has no end date (always active)
                    ->orWhere('end_date', '>=', $now->toDateString()); // Or end date is in the future
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at') // Coupon has no specific expiry time
                    ->orWhere('expires_at', '>=', $now); // Or expiry time is in the future
            })
            // You might add conditions like:
            // ->where('usage_limit', '>', 0) // if you have a usage limit
            // ->whereDoesntHave('users', function ($query) use ($request) {
            //     $query->where('user_id', $request->user()->id); // if each user can use it only once
            // })
            ->get();

        // You might want to filter sensitive information before sending to frontend,
        // though for display, these fields are usually fine.
        return response()->json($availableCoupons, Response::HTTP_OK);
    }
}
