<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Payment; // Đảm bảo đã import
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

// Import các service hoặc class xử lý payment gateways (sẽ thêm sau)
// use App\Services\MomoPaymentService;
// use App\Services\VnPayPaymentService;

class CheckoutController extends Controller
{
    /**
     * Lấy chi tiết các sản phẩm được chọn từ giỏ hàng để hiển thị ở trang checkout.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCheckoutItems(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
        ]);

        $cartItemIds = $request->input('cart_item_ids');

        $checkoutItems = CartItem::whereIn('id', $cartItemIds)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'active');
            })
            ->with(['variant.product', 'variant.attributeValues.attribute']) // Eager load product and variant details
            ->get();

        if ($checkoutItems->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm hợp lệ trong giỏ hàng của bạn.'], Response::HTTP_NOT_FOUND);
        }

        $formattedItems = [];
        foreach ($checkoutItems as $item) {
            $variant = $item->variant;
            if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                return response()->json([
                    'message' => 'Sản phẩm "' . ($variant->product->name ?? 'Không xác định') .
                        ' (' . ($variant->sku ?? 'SKU không rõ') . ')' .
                        '" không đủ tồn kho (' . ($variant->stock ?? 0) . ' còn lại) hoặc không có sẵn.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Xây dựng tên biến thể
            $variantName = $variant->product->name ?? 'Sản phẩm không rõ';
            $attributeParts = [];
            if ($variant->relationLoaded('attributeValues') && $variant->attributeValues->isNotEmpty()) {
                foreach ($variant->attributeValues as $attrValue) {
                    if ($attrValue->relationLoaded('attribute') && $attrValue->attribute && $attrValue->value) {
                        $attributeParts[] = $attrValue->attribute->name . ': ' . $attrValue->value;
                    }
                }
            }
            if (!empty($attributeParts)) {
                $variantName .= ' (' . implode(' / ', $attributeParts) . ')';
            }

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $variant->product->id ?? null,
                'product_name' => $variant->product->name ?? 'Sản phẩm không rõ',
                'thumbnail_url' => $variant->product->thumbnail_url ?? 'https://via.placeholder.com/64',
                'price' => $variant->price,
                'quantity' => $item->quantity,
                'variant' => [
                    'id' => $variant->id,
                    'name' => $variantName, // Tên biến thể đầy đủ
                    'sku' => $variant->sku,
                ],
                'subtotal' => $variant->price * $item->quantity, // Thêm subtotal cho frontend tính toán
            ];
        }

        return response()->json(['items' => $formattedItems]);
    }

    /**
     * Lấy chi tiết biến thể sản phẩm cho chức năng "Mua ngay".
     * @param int $variantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductVariantDetails(int $variantId)
    {
        $variant = ProductVariant::with('product', 'attributeValues.attribute')->find($variantId);

        if (!$variant) {
            return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm.'], Response::HTTP_NOT_FOUND);
        }

        // Xây dựng tên biến thể tương tự như trong getCheckoutItems
        $variantName = $variant->product->name ?? 'Sản phẩm không rõ';
        $attributeParts = [];
        if ($variant->relationLoaded('attributeValues') && $variant->attributeValues->isNotEmpty()) {
            foreach ($variant->attributeValues as $attrValue) {
                if ($attrValue->relationLoaded('attribute') && $attrValue->attribute && $attrValue->value) {
                    $attributeParts[] = $attrValue->attribute->name . ': ' . $attrValue->value;
                }
            }
        }
        if (!empty($attributeParts)) {
            $variantName .= ' (' . implode(' / ', $attributeParts) . ')';
        }

        return response()->json([
            'data' => [
                'id' => $variant->id,
                'product_id' => $variant->product->id ?? null,
                'product' => [ // Trả về thông tin product đầy đủ hơn cho frontend
                    'id' => $variant->product->id ?? null,
                    'name' => $variant->product->name ?? 'Sản phẩm không rõ',
                    'image' => $variant->product->thumbnail_url ?? 'https://via.placeholder.com/64',
                ],
                'price' => $variant->price,
                'stock' => $variant->stock,
                'sku' => $variant->sku,
                'status' => $variant->status,
                'name' => $variantName, // Tên biến thể đầy đủ
                'attribute_values' => $variant->attributeValues->map(function ($av) {
                    return [
                        'id' => $av->id,
                        'value' => $av->value,
                        'attribute' => ['name' => $av->attribute->name ?? null]
                    ];
                }),
                // Add any other relevant variant details
            ]
        ]);
    }


    /**
     * Xử lý đặt hàng từ giỏ hàng.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder(Request $request)
    {
        $user = $request->user();

        // Lấy danh sách các phương thức thanh toán hợp lệ từ model Payment
        $allowedPaymentMethods = Payment::PAYMENT_METHODS;

        $cart = Cart::with([
            'items.variant' => function ($query) {
                $query->with('product', 'attributeValues.attribute');
            }
        ])->where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng rỗng.'], Response::HTTP_BAD_REQUEST);
        }

        $validated = $request->validate([
            'address_id' => 'nullable|exists:user_addresses,id',
            'recipient_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|string|in:' . implode(',', $allowedPaymentMethods), // Cập nhật validation
        ]);

        $addressData = $this->resolveAddressData($user, $validated);
        if ($addressData instanceof \Illuminate\Http\JsonResponse) {
            return $addressData; // Return error response from resolveAddressData
        }

        DB::beginTransaction();

        try {
            $total = 0;
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                    DB::rollBack();
                    return response()->json(['message' => 'Sản phẩm "' . ($variant->sku ?? 'không xác định') . '" không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
                }
                $total += $variant->price * $item->quantity;
            }

            $shippingFee = 0; // Có thể tính phí vận chuyển ở đây dựa trên địa chỉ

            $coupon = null;
            $couponDiscount = 0;
            $finalTotal = $total;

            if (!empty($validated['coupon_code'])) {
                $couponResult = $this->applyCoupon($validated['coupon_code'], $total, $user);
                if ($couponResult instanceof \Illuminate\Http\JsonResponse) {
                    DB::rollBack();
                    return $couponResult; // Return error response from applyCoupon
                }
                $coupon = $couponResult['coupon'];
                $couponDiscount = $couponResult['discount'];
                $finalTotal = max(0, $total - $couponDiscount);
            }

            $finalTotal += $shippingFee;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'payment_method' => $validated['payment_method'],
                'shipping_fee' => $shippingFee,
                // 'payment_status' => 'pending', // Tùy chọn, có thể quản lý qua bảng payments
            ]);

            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            foreach ($cart->items as $item) {
                $variant = $item->variant;
                $variantName = $variant->product->name;
                $attributeParts = [];
                if ($variant->relationLoaded('attributeValues')) {
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
                    'variant_name' => $variantName,
                    'variant_status' => $variant->status,
                    'variant_description' => $variant->description,
                ]);
                $variant->decrement('stock', $item->quantity);
                $variant->increment('sold', $item->quantity);
            }

            $cart->update(['status' => 'converted']);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Gọi hàm xử lý thanh toán
            $paymentResult = $this->processPayment($order, $validated['payment_method'], $finalTotal);

            // Cập nhật trạng thái đơn hàng dựa trên kết quả thanh toán
            if ($paymentResult['status'] === 'completed') {
                $order->update(['status' => 'processing']);
            } elseif ($paymentResult['status'] === 'redirect') {
                // Đơn hàng chờ chuyển hướng thanh toán (Momo, VnPay)
                $order->update(['status' => 'pending_payment']);
            } else {
                // Có lỗi trong quá trình xử lý payment
                $order->update(['status' => 'payment_failed']);
                DB::rollBack();
                return response()->json(['message' => $paymentResult['message']], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id,
                'payment_info' => $paymentResult,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng từ giỏ: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Có lỗi khi đặt hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Xử lý chức năng "Mua ngay".
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyNow(Request $request)
    {
        $user = $request->user();

        $allowedPaymentMethods = Payment::PAYMENT_METHODS;

        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'address_id' => 'nullable|exists:user_addresses,id',
            'recipient_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_method' => 'required|string|in:' . implode(',', $allowedPaymentMethods), // Cập nhật validation
        ]);

        $addressData = $this->resolveAddressData($user, $validated);
        if ($addressData instanceof \Illuminate\Http\JsonResponse) {
            return $addressData; // Return error response from resolveAddressData
        }

        $variant = ProductVariant::with('product', 'attributeValues.attribute')->findOrFail($validated['product_variant_id']);

        if ($variant->stock < $validated['quantity'] || $variant->status === 'unavailable') {
            return response()->json(['message' => 'Sản phẩm không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
        }

        $total = $variant->price * $validated['quantity'];
        $shippingFee = 0; // Có thể tính phí vận chuyển ở đây

        $coupon = null;
        $couponDiscount = 0;
        $finalTotal = $total;

        if (!empty($validated['coupon_code'])) {
            $couponResult = $this->applyCoupon($validated['coupon_code'], $total, $user);
            if ($couponResult instanceof \Illuminate\Http\JsonResponse) {
                return $couponResult; // Return error response from applyCoupon
            }
            $coupon = $couponResult['coupon'];
            $couponDiscount = $couponResult['discount'];
            $finalTotal = max(0, $total - $couponDiscount);
        }

        $finalTotal += $shippingFee;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'payment_method' => $validated['payment_method'],
                'shipping_fee' => $shippingFee,
            ]);

            $variantName = $variant->product->name;
            $attributeParts = [];
            if ($variant->relationLoaded('attributeValues')) {
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
                'quantity' => $validated['quantity'],
                'price_each' => $variant->price,
                'variant_sku' => $variant->sku,
                'variant_name' => $variantName,
                'variant_status' => $variant->status,
                'variant_description' => $variant->description,
            ]);

            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            $variant->decrement('stock', $validated['quantity']);
            $variant->increment('sold', $validated['quantity']);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Gọi hàm xử lý thanh toán
            $paymentResult = $this->processPayment($order, $validated['payment_method'], $finalTotal);

            // Cập nhật trạng thái đơn hàng dựa trên kết quả thanh toán
            if ($paymentResult['status'] === 'completed') {
                $order->update(['status' => 'processing']);
            } elseif ($paymentResult['status'] === 'redirect') {
                $order->update(['status' => 'pending_payment']);
            } else {
                $order->update(['status' => 'payment_failed']);
                DB::rollBack();
                return response()->json(['message' => $paymentResult['message']], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();
            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id,
                'payment_info' => $paymentResult,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi mua ngay: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Có lỗi xảy ra khi mua hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Xử lý các phương thức thanh toán.
     * @param Order $order
     * @param string $paymentMethod
     * @param float $amount
     * @return array
     */
    private function processPayment(Order $order, string $paymentMethod, float $amount): array
    {
        $paymentStatus = 'pending'; // Trạng thái mặc định cho các phương thức online
        $paidAt = null;
        $transactionId = null;
        $payerId = null;
        $paymentDetails = null;
        $redirectUrl = null; // Dành cho các cổng thanh toán cần redirect

        try {
            switch ($paymentMethod) {
                case 'cash':
                    $paymentStatus = 'paid'; // COD được coi là "paid" ngay khi đơn hàng được tạo thành công
                    $paidAt = now();
                    $message = 'Thanh toán khi nhận hàng (COD). Đơn hàng sẽ được xử lý.';
                    break;

                case 'momo':
                    // TODO: Gọi MomoPaymentService để tạo yêu cầu thanh toán
                    // Ví dụ:
                    // $momoService = new MomoPaymentService();
                    // $momoResponse = $momoService->createPayment($order->id, $amount, $order->order_code);
                    // if ($momoResponse['status'] === 'success') {
                    //     $transactionId = $momoResponse['transactionId'];
                    //     $redirectUrl = $momoResponse['payUrl'];
                    //     $paymentDetails = $momoResponse['rawResponse']; // Lưu toàn bộ response từ Momo
                    //     $message = 'Đang chuyển hướng đến cổng thanh toán Momo...';
                    //     $paymentStatus = 'pending'; // Hoặc 'awaiting_payment'
                    // } else {
                    //     $message = 'Lỗi khi tạo yêu cầu thanh toán Momo: ' . $momoResponse['message'];
                    //     $paymentStatus = 'failed';
                    // }
                    // Tạm thời để pending và message cho momo
                    $paymentStatus = 'pending';
                    $message = 'Thanh toán qua Momo đang được phát triển.';
                    break;

                case 'vnpay':
                    // TODO: Gọi VnPayPaymentService để tạo yêu cầu thanh toán
                    // Ví dụ:
                    // $vnpayService = new VnPayPaymentService();
                    // $vnpayResponse = $vnpayService->createPayment($order->id, $amount, $order->order_code);
                    // if ($vnpayResponse['status'] === 'success') {
                    //     $transactionId = $vnpayResponse['transactionId'];
                    //     $redirectUrl = $vnpayResponse['payUrl'];
                    //     $paymentDetails = $vnpayResponse['rawResponse']; // Lưu toàn bộ response từ VnPay
                    //     $message = 'Đang chuyển hướng đến cổng thanh toán VNPAY...';
                    //     $paymentStatus = 'pending';
                    // } else {
                    //     $message = 'Lỗi khi tạo yêu cầu thanh toán VNPAY: ' . $vnpayResponse['message'];
                    //     $paymentStatus = 'failed';
                    // }
                    // Tạm thời để pending và message cho vnpay
                    $paymentStatus = 'pending';
                    $message = 'Thanh toán qua VNPAY đang được phát triển.';
                    break;

                default:
                    $message = 'Phương thức thanh toán không hợp lệ.';
                    $paymentStatus = 'failed';
                    break;
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'transaction_id' => $transactionId,
                'payer_id' => $payerId,
                'payment_status' => $paymentStatus,
                'paid_at' => $paidAt,
                'payment_details' => $paymentDetails,
            ]);

            $result = [
                'status' => ($payment->payment_status === 'paid' || $payment->payment_status === 'pending') ? 'completed' : 'failed', // "completed" cho cả paid và pending (nếu có redirect)
                'message' => $message,
                'payment_status' => $payment->payment_status,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at ? $payment->paid_at->toDateTimeString() : null,
                'transaction_id' => $payment->transaction_id,
            ];

            if ($redirectUrl) {
                $result['status'] = 'redirect'; // Đánh dấu là cần redirect
                $result['redirect_url'] = $redirectUrl;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Lỗi khi xử lý Payment cho Order ID {$order->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'failed',
                'message' => 'Lỗi hệ thống khi xử lý thanh toán: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Lấy danh sách các coupon có sẵn và trạng thái khả dụng.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableCoupons(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $allValidCoupons = Coupon::where(function ($query) use ($now) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get();

        $formattedCoupons = $allValidCoupons->map(function ($coupon) use ($user, $now) {
            $isUsable = true;
            $reason = null;

            if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                $isUsable = false;
                $reason = 'Mã giảm giá đã hết lượt sử dụng.';
            }

            if ($isUsable && $coupon->per_user_limit !== null) {
                $userUsedCount = Order::where('user_id', $user->id)
                    ->where('coupon_id', $coupon->id)
                    ->whereIn('status', ['pending', 'processing', 'completed', 'shipped', 'pending_payment']) // Thêm 'pending_payment'
                    ->count();
                if ($userUsedCount >= $coupon->per_user_limit) {
                    $isUsable = false;
                    $reason = 'Bạn đã sử dụng mã giảm giá này quá số lần cho phép.';
                }
            }

            if ($coupon->start_date && $coupon->start_date->isFuture()) {
                $isUsable = false;
                $reason = 'Mã giảm giá chưa đến ngày bắt đầu sử dụng.';
            }

            $couponData = $coupon->toArray();
            $couponData['is_usable'] = $isUsable;
            $couponData['unusable_reason'] = $reason;

            return $couponData;
        });

        $sortedCoupons = $formattedCoupons->sortByDesc('is_usable')->values();

        return response()->json($sortedCoupons, Response::HTTP_OK);
    }

    // --- Các phương thức hỗ trợ (private methods) ---

    /**
     * Giải quyết dữ liệu địa chỉ từ request.
     * @param \App\Models\User $user
     * @param array $validatedData
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function resolveAddressData($user, array $validatedData)
    {
        if (!empty($validatedData['address_id'])) {
            $userAddress = UserAddress::where('id', $validatedData['address_id'])->where('user_id', $user->id)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Địa chỉ đã chọn không hợp lệ hoặc không thuộc về bạn.'], Response::HTTP_BAD_REQUEST);
            }
            return $userAddress->toArray();
        } elseif (
            isset($validatedData['recipient_name']) &&
            isset($validatedData['phone_number']) &&
            isset($validatedData['address_line']) &&
            isset($validatedData['ward']) &&
            isset($validatedData['district']) &&
            isset($validatedData['province'])
        ) {
            // Validate required fields for new address if they are provided
            // (already handled by the main validation, but good to be explicit)
            if (!($validatedData['recipient_name'] && $validatedData['phone_number'] &&
                $validatedData['address_line'] && $validatedData['ward'] &&
                $validatedData['district'] && $validatedData['province'])) {
                return response()->json(['message' => 'Vui lòng điền đầy đủ thông tin địa chỉ mới.'], Response::HTTP_BAD_REQUEST);
            }
            return [
                'recipient_name' => $validatedData['recipient_name'],
                'phone_number' => $validatedData['phone_number'],
                'address_line' => $validatedData['address_line'],
                'ward' => $validatedData['ward'],
                'district' => $validatedData['district'],
                'province' => $validatedData['province'],
            ];
        } else {
            $userAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
            if (!$userAddress) {
                return response()->json(['message' => 'Bạn cần cung cấp thông tin địa chỉ giao hàng hoặc chọn một địa chỉ có sẵn.'], Response::HTTP_BAD_REQUEST);
            }
            return $userAddress->toArray();
        }
    }

    /**
     * Áp dụng mã giảm giá.
     * @param string $couponCode
     * @param float $totalOrderAmount
     * @param \App\Models\User $user
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function applyCoupon(string $couponCode, float $totalOrderAmount, $user)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Mã giảm giá không hợp lệ.'], Response::HTTP_BAD_REQUEST);
        }

        $now = Carbon::now();
        if ($coupon->start_date && $coupon->start_date->isFuture()) {
            return response()->json(['message' => 'Mã giảm giá chưa đến ngày bắt đầu sử dụng.'], Response::HTTP_BAD_REQUEST);
        }
        if ($coupon->end_date && $coupon->end_date->isPast()) {
            return response()->json(['message' => 'Mã giảm giá đã hết hạn.'], Response::HTTP_BAD_REQUEST);
        }

        if ($coupon->min_order_amount && $totalOrderAmount < $coupon->min_order_amount) {
            return response()->json(['message' => 'Mã giảm giá chỉ áp dụng cho đơn hàng từ ' . number_format($coupon->min_order_amount) . ' VND trở lên.'], Response::HTTP_BAD_REQUEST);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['message' => 'Mã giảm giá đã đạt giới hạn số lần sử dụng.'], Response::HTTP_BAD_REQUEST);
        }

        if ($coupon->per_user_limit !== null) {
            $userUsedCount = Order::where('user_id', $user->id)
                ->where('coupon_id', $coupon->id)
                ->whereIn('status', ['pending', 'processing', 'completed', 'shipped', 'pending_payment'])
                ->count();
            if ($userUsedCount >= $coupon->per_user_limit) {
                return response()->json(['message' => 'Bạn đã sử dụng mã giảm giá này quá số lần cho phép.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $discount = $coupon->discount_type === 'percent' ? ($totalOrderAmount * $coupon->discount_value / 100) : $coupon->discount_value;

        if ($coupon->max_discount !== null && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        return ['coupon' => $coupon, 'discount' => $discount];
    }
}
