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
use Exception;

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

        // Log đầu vào của request
        Log::info('getCheckoutItems: Request received.', ['user_id' => $user->id, 'request_data' => $request->all()]);

        $request->validate([
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
        ]);

        $cartItemIds = $request->input('cart_item_ids');

        Log::info('getCheckoutItems: Validated cart_item_ids.', ['cart_item_ids' => $cartItemIds]);

        $checkoutItems = CartItem::whereIn('id', $cartItemIds)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'active');
            })
            ->with(['variant.product', 'variant.attributeValues.attribute'])
            ->get();

        // Log kết quả truy vấn CartItem
        Log::info('getCheckoutItems: Fetched cart items.', ['count' => $checkoutItems->count(), 'items_data' => $checkoutItems->toArray()]);


        if ($checkoutItems->isEmpty()) {
            Log::warning('getCheckoutItems: No valid cart items found for user ' . $user->id . ' with IDs ' . implode(',', $cartItemIds));
            return response()->json(['message' => 'Không tìm thấy sản phẩm hợp lệ trong giỏ hàng của bạn.'], Response::HTTP_NOT_FOUND);
        }

        $formattedItems = [];
        foreach ($checkoutItems as $item) {
            $variant = $item->variant;

            // Log thông tin biến thể trước khi kiểm tra điều kiện
            Log::info('getCheckoutItems: Processing cart item ID ' . $item->id, [
                'variant_id' => $variant->id ?? 'N/A',
                'quantity_in_cart' => $item->quantity,
                'variant_stock' => $variant->stock ?? 'N/A',
                'variant_status' => $variant->status ?? 'N/A',
                'product_name' => $variant->product->name ?? 'N/A',
            ]);

            if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                $productName = $variant->product->name ?? 'Không xác định';
                $sku = $variant->sku ?? 'SKU không rõ';
                $stock = $variant->stock ?? 0;

                Log::warning('getCheckoutItems: Item ' . $item->id . ' is invalid.', [
                    'reason' => !$variant ? 'Variant not found' : ($variant->stock < $item->quantity ? 'Insufficient stock' : 'Variant unavailable'),
                    'product_name' => $productName,
                    'sku' => $sku,
                    'requested_qty' => $item->quantity,
                    'available_stock' => $stock
                ]);

                return response()->json([
                    'message' => 'Sản phẩm "' . $productName .
                        ' (' . $sku . ')' .
                        '" không đủ tồn kho (' . $stock . ' còn lại) hoặc không có sẵn.'
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
                'subtotal' => $variant->price * $item->quantity,
            ];
        }

        // Log dữ liệu cuối cùng trước khi trả về
        Log::info('getCheckoutItems: Successfully formatted items.', ['count' => count($formattedItems), 'items' => $formattedItems]);

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

        // LOG 1: Kiểm tra payload nhận được từ frontend
        Log::info('Nhận yêu cầu đặt hàng:', $request->all());

        $allowedPaymentMethods = Payment::PAYMENT_METHODS;

        $cart = Cart::with([
            'items.variant' => function ($query) {
                $query->with('product', 'attributeValues.attribute');
            }
        ])->where('user_id', $user->id)->where('status', 'active')->first();

        if (!$cart || $cart->items->isEmpty()) {
            Log::warning('Giỏ hàng rỗng khi đặt hàng cho user: ' . $user->id);
            return response()->json(['message' => 'Giỏ hàng rỗng.'], Response::HTTP_BAD_REQUEST);
        }

        // LOG 2: Kiểm tra dữ liệu giỏ hàng
        Log::info('Giỏ hàng được tìm thấy:', ['cart_id' => $cart->id, 'item_count' => $cart->items->count()]);

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
            'payment_method' => 'required|string|in:' . implode(',', $allowedPaymentMethods),
        ]);

        // LOG 3: Kiểm tra dữ liệu đã được validate
        Log::info('Dữ liệu đã validate:', $validated);

        $addressData = $this->resolveAddressData($user, $validated);
        if ($addressData instanceof \Illuminate\Http\JsonResponse) {
            // LOG 4: Lỗi từ resolveAddressData
            Log::warning('Lỗi địa chỉ từ resolveAddressData cho user ' . $user->id . ': ' . $addressData->getContent());
            return $addressData;
        }

        // LOG 5: Dữ liệu địa chỉ sau khi resolve
        Log::info('Dữ liệu địa chỉ đã được giải quyết:', $addressData);


        DB::beginTransaction();

        try {
            $totalItemsPrice = 0; // Tổng giá trị các sản phẩm trong giỏ
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                // Kiểm tra tồn kho và trạng thái của sản phẩm ngay trước khi đặt hàng
                if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                    DB::rollBack();
                    // LOG 6: Lỗi tồn kho/trạng thái sản phẩm
                    Log::warning('Sản phẩm không đủ tồn kho/không sẵn: ', [
                        'user_id' => $user->id,
                        'cart_item_id' => $item->id,
                        'variant_id' => $variant->id ?? 'N/A',
                        'requested_quantity' => $item->quantity,
                        'available_stock' => $variant->stock ?? 'N/A',
                        'status' => $variant->status ?? 'N/A'
                    ]);
                    return response()->json(['message' => 'Sản phẩm "' . ($variant->product->name ?? 'không xác định') . '" biến thể "' . ($variant->sku ?? 'không xác định') . '" không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
                }
                $totalItemsPrice += $variant->price * $item->quantity;
            }

            // LOG 7: Tổng tiền sản phẩm trước khi tính phí ship/voucher
            Log::info('Tổng tiền sản phẩm (totalItemsPrice): ' . $totalItemsPrice);

            $shippingFee = 0; // Cần thay thế bằng logic tính phí vận chuyển thực tế
            // Ví dụ: $shippingFee = $this->calculateShippingFee($addressData, $totalItemsPrice);

            $coupon = null;
            $couponDiscount = 0;
            $finalTotal = $totalItemsPrice; // Khởi tạo tổng cuối cùng với tổng giá sản phẩm

            // Áp dụng mã giảm giá nếu có
            if (!empty($validated['coupon_code'])) {
                try {
                    $couponResult = $this->applyCoupon($validated['coupon_code'], $totalItemsPrice, $user);
                    $coupon = $couponResult['coupon'];
                    $couponDiscount = $couponResult['discount'];
                    $finalTotal = max(0, $totalItemsPrice - $couponDiscount); // Đảm bảo tổng tiền không âm sau giảm giá
                    // LOG 8: Thông tin voucher đã áp dụng
                    Log::info('Voucher đã áp dụng:', ['code' => $validated['coupon_code'], 'discount' => $couponDiscount]);
                } catch (Exception $e) {
                    DB::rollBack();
                    // LOG 9: Lỗi khi áp dụng voucher
                    Log::warning('Lỗi khi áp dụng voucher cho user ' . $user->id . ': ' . $e->getMessage());
                    return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
                }
            }

            $finalTotal += $shippingFee; // Cộng phí vận chuyển vào tổng cuối cùng

            // LOG 10: Tổng tiền cuối cùng trước khi tạo order
            Log::info('Tổng tiền cuối cùng trước khi tạo Order (finalTotal): ' . $finalTotal);


            // Tạo đơn hàng (Order)
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal, // Tổng giá sau giảm giá + phí vận chuyển
                'status' => Order::STATUS_PENDING, // Trạng thái ban đầu
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id, // Lưu ID coupon nếu có
                'shipping_fee' => $shippingFee,
                'discount_amount' => $couponDiscount, // Lưu số tiền giảm giá đã áp dụng
            ]);

            // LOG 11: Đơn hàng đã tạo
            Log::info('Đã tạo đơn hàng:', ['order_id' => $order->id, 'total_price' => $order->total_price]);

            // Tạo địa chỉ cho đơn hàng (OrderAddress)
            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            // LOG 12: Địa chỉ đơn hàng đã tạo
            Log::info('Đã tạo địa chỉ cho đơn hàng:', ['order_id' => $order->id, 'address' => $addressData]);


            // Tạo các mục đơn hàng (OrderItems) và cập nhật tồn kho
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                // Xây dựng tên biến thể (variant name) chi tiết để lưu vào OrderItem
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
                // Giảm tồn kho và tăng số lượng đã bán
                $variant->decrement('stock', $item->quantity);
                $variant->increment('sold', $item->quantity);
                // LOG 13: Cập nhật tồn kho cho mỗi item
                Log::info('Đã xử lý item đơn hàng và cập nhật tồn kho:', [
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'quantity' => $item->quantity,
                    'new_stock' => $variant->stock
                ]);
            }

            // Đánh dấu giỏ hàng là đã chuyển đổi thành đơn hàng
            $cart->update(['status' => 'converted']);
            Log::info('Giỏ hàng đã được đánh dấu là "converted": ' . $cart->id);


            // Tăng used_count của coupon nếu được sử dụng
            if ($coupon) {
                $coupon->increment('used_count');
                Log::info('Used count của coupon đã tăng: ' . $coupon->code);
            }

            // Xử lý thanh toán và tạo bản ghi trong bảng `payments`
            $paymentResult = $this->processPayment($order, $validated['payment_method'], $finalTotal);
            // LOG 14: Kết quả từ processPayment
            Log::info('Kết quả xử lý thanh toán:', $paymentResult);


            // Cập nhật trạng thái đơn hàng dựa trên kết quả thanh toán từ processPayment
            if ($paymentResult['status'] === Payment::PAYMENT_STATUS_PAID || $paymentResult['status'] === 'completed') { // <-- THÊM ĐIỀU KIỆN 'completed' VÀO ĐÂY
                $order->update(['status' => Order::STATUS_PROCESSING]); // Đã thanh toán, chuyển sang xử lý
            } elseif ($paymentResult['status'] === Payment::PAYMENT_STATUS_PENDING) {
                $order->update(['status' => Order::STATUS_PENDING]); // COD hoặc thanh toán đang chờ xử lý
            } elseif ($paymentResult['status'] === 'redirect') {
                $order->update(['status' => Order::STATUS_PENDING_PAYMENT]);
            } else {
                // Có lỗi trong quá trình xử lý payment
                $order->update(['status' => Order::STATUS_PAYMENT_FAILED]);
                DB::rollBack();
                Log::error('Lỗi trong quá trình xử lý thanh toán cho order ' . $order->id . ': ' . ($paymentResult['message'] ?? 'Không rõ lỗi.'));
                return response()->json(['message' => $paymentResult['message']], Response::HTTP_BAD_REQUEST);
            }

            DB::commit(); // Hoàn tất transaction nếu mọi thứ thành công
            Log::info('Transaction đặt hàng thành công cho user: ' . $user->id . ' order: ' . $order->id);

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id,
                'payment_info' => $paymentResult,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback nếu có bất kỳ lỗi nào xảy ra
            // LOG 16: Lỗi tổng quát trong quá trình đặt hàng
            Log::error('Lỗi tổng quát khi đặt hàng từ giỏ cho user ' . $user->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Có lỗi khi đặt hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Phương thức giải quyết dữ liệu địa chỉ (không thay đổi)
     * @param \App\Models\User $user
     * @param array $validatedData
     * @return array|\Illuminate\Http\JsonResponse
     */

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

        // Lọc các coupon đang hoạt động và không hết hạn theo thời gian
        $allValidCoupons = Coupon::where('is_active', true) // Chỉ lấy coupon đang hoạt động
            ->where(function ($query) use ($now) {
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

            // Sử dụng các helper method từ model Coupon
            if ($coupon->isUsageLimitReached()) {
                $isUsable = false;
                $reason = 'Mã giảm giá đã hết lượt sử dụng tổng cộng.';
            }

            if ($isUsable && $coupon->per_user_limit !== null) {
                $userUsedCount = Order::where('user_id', $user->id)
                    ->where('coupon_id', $coupon->id)
                    ->whereIn('status', ['pending', 'processing', 'completed', 'shipped', 'pending_payment'])
                    ->count();
                if ($userUsedCount >= $coupon->per_user_limit) {
                    $isUsable = false;
                    $reason = 'Bạn đã sử dụng mã giảm giá này quá số lần cho phép.';
                }
            }

            // Mặc dù đã lọc ở query, nhưng kiểm tra lại để đảm bảo logic nhất quán
            if ($coupon->isExpired()) {
                $isUsable = false;
                $reason = 'Mã giảm giá đã hết hạn.';
            }
            if ($coupon->isYetToStart()) {
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

    public function checkCoupon(Request $request) // <--- TẠO PHƯƠNG THỨC NÀY
    {
        $user = $request->user(); // Lấy thông tin người dùng đã xác thực

        // 1. Validate dữ liệu đầu vào từ frontend
        $request->validate([
            'coupon_code' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0', // Đảm bảo frontend gửi trường này
        ]);

        $couponCode = $request->input('coupon_code');
        $totalAmount = $request->input('total_amount');

        try {
            // 2. Gọi phương thức private applyCoupon() để thực hiện logic chính
            $result = $this->applyCoupon($couponCode, $totalAmount, $user);

            // Nếu không có lỗi, applyCoupon() sẽ trả về mảng ['coupon', 'discount']
            $coupon = $result['coupon'];
            $discount = $result['discount'];

            // 3. Trả về phản hồi thành công cho frontend
            return response()->json([
                'message' => 'Mã giảm giá hợp lệ.',
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'discount_amount' => $discount, // Giá trị giảm giá đã được tính
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            // 4. Bắt Exception do applyCoupon() ném ra và trả về phản hồi lỗi
            Log::warning('checkCoupon: Coupon application failed.', [
                'coupon_code' => $couponCode,
                'user_id' => $user->id,
                'error_message' => $e->getMessage()
            ]);
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
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
     * Phương thức riêng tư để áp dụng và kiểm tra mã giảm giá.
     * Chỉ được gọi nội bộ từ các phương thức công khai khác trong Controller này.
     *
     * @param string $couponCode
     * @param float $totalOrderAmount
     * @param \App\Models\User $user
     * @return array Contains 'coupon' model and 'discount' amount.
     * @throws \Exception If the coupon is invalid or conditions are not met.
     */
    private function applyCoupon(string $couponCode, float $totalOrderAmount, $user): array // <-- Đảm bảo vẫn là private
    {
        $coupon = Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            throw new Exception('Mã giảm giá không hợp lệ hoặc không hoạt động.');
        }

        if (method_exists($coupon, 'isYetToStart') && $coupon->isYetToStart()) {
            throw new Exception('Mã giảm giá chưa đến ngày bắt đầu sử dụng.');
        }
        if (method_exists($coupon, 'isExpired') && $coupon->isExpired()) {
            throw new Exception('Mã giảm giá đã hết hạn.');
        }

        if (method_exists($coupon, 'meetsMinOrderAmount') && !$coupon->meetsMinOrderAmount($totalOrderAmount)) {
            throw new Exception('Mã giảm giá chỉ áp dụng cho đơn hàng từ ' . number_format($coupon->min_order_amount) . ' VND trở lên.');
        }

        if (method_exists($coupon, 'isUsageLimitReached') && $coupon->isUsageLimitReached()) {
            throw new Exception('Mã giảm giá đã đạt giới hạn số lần sử dụng.');
        }

        if ($coupon->per_user_limit !== null) {
            $userUsedCount = Order::where('user_id', $user->id)
                ->where('coupon_id', $coupon->id)
                ->whereIn('status', ['pending', 'processing', 'completed', 'shipped', 'delivered', 'pending_payment'])
                ->count();
            if ($userUsedCount >= $coupon->per_user_limit) {
                throw new Exception('Bạn đã sử dụng mã giảm giá này quá số lần cho phép.');
            }
        }

        if (!method_exists($coupon, 'calculateDiscount')) {
            throw new Exception('Lỗi hệ thống: Không thể tính toán giảm giá cho mã coupon.');
        }
        $discount = $coupon->calculateDiscount($totalOrderAmount);

        return ['coupon' => $coupon, 'discount' => $discount];
    }
}
