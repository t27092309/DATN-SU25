<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use App\Models\ShippingMethod; // Import the ShippingMethod model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;

// Import các service hoặc class xử lý payment gateways
use App\Services\MomoPaymentService;
use App\Services\VnPayPaymentService;

class CheckoutController extends Controller
{

    protected $momoService;
    protected $vnpayService;

    // Constructor to inject services (recommended)
    public function __construct(MomoPaymentService $momoService, VnPayPaymentService $vnpayService)
    {
        $this->momoService = $momoService;
        $this->vnpayService = $vnpayService;
    }
    /**
     * Lấy chi tiết các sản phẩm được chọn từ giỏ hàng để hiển thị ở trang checkout.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCheckoutItems(Request $request)
    {
        $user = $request->user();

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

        Log::info('getCheckoutItems: Fetched cart items.', ['count' => $checkoutItems->count(), 'items_data' => $checkoutItems->toArray()]);


        if ($checkoutItems->isEmpty()) {
            Log::warning('getCheckoutItems: No valid cart items found for user ' . $user->id . ' with IDs ' . implode(',', $cartItemIds));
            return response()->json(['message' => 'Không tìm thấy sản phẩm hợp lệ trong giỏ hàng của bạn.'], Response::HTTP_NOT_FOUND);
        }

        $formattedItems = [];
        foreach ($checkoutItems as $item) {
            $variant = $item->variant;

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
            $thumbnailUrl = 'https://via.placeholder.com/64'; // Default placeholder

            // Kiểm tra xem product và thumbnail_url có tồn tại không
            if ($variant->product && $variant->product->image) {
                // Lấy đường dẫn tương đối từ database
                $relativePath = $variant->product->image;
                $thumbnailUrl = config('app.url') . '/' . ltrim(Storage::url($relativePath), '/');
            }
            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $variant->product->id ?? null,
                'product_name' => $variant->product->name ?? 'Sản phẩm không rõ',
                'thumbnail_url' => $thumbnailUrl,
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
        $thumbnailUrl = 'https://via.placeholder.com/64'; // Default placeholder

        // Kiểm tra xem product và thumbnail_url có tồn tại không
        if ($variant->product && $variant->product->thumbnail_url) {
            // Lấy đường dẫn tương đối từ database
            $relativePath = $variant->product->thumbnail_url;

            // Chuyển đổi thành đường dẫn tuyệt đối
            // config('app.url') sẽ lấy giá trị từ .env APP_URL, ví dụ: http://localhost:8000
            // Storage::url() sẽ tạo đường dẫn công khai cho file trong storage
            // ltrim(..., '/') để đảm bảo không có dấu // nếu Storage::url đã thêm / ở đầu
            $thumbnailUrl = config('app.url') . '/' . ltrim(Storage::url($relativePath), '/');
        }
        return response()->json([
            'data' => [
                'id' => $variant->id,
                'product_id' => $variant->product->id ?? null,
                'product' => [ // Trả về thông tin product đầy đủ hơn cho frontend
                    'id' => $variant->product->id ?? null,
                    'name' => $variant->product->name ?? 'Sản phẩm không rõ',
                    'image' => $thumbnailUrl ?? 'https://via.placeholder.com/64',
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
     * Lấy danh sách các phương thức vận chuyển đang hoạt động.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveShippingMethods()
    {
        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        if ($shippingMethods->isEmpty()) {
            return response()->json(['message' => 'Không có phương thức vận chuyển nào khả dụng.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['shipping_methods' => $shippingMethods]);
    }

    /**
     * Xử lý đặt hàng từ giỏ hàng.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder(Request $request)
    {
        $user = $request->user();

        Log::info('Nhận yêu cầu đặt hàng:', $request->all());

        $allowedPaymentMethods = Payment::PAYMENT_METHODS;

        $validated = $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
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
            'shipping_method_id' => 'required|exists:shipping_methods,id,is_active,1',
        ]);

        $cartItemIds = $validated['cart_item_ids'];

        // --- Thay đổi lớn ở đây: Chỉ lấy các cart items được chọn ---
        $cartItems = CartItem::whereIn('id', $cartItemIds)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['variant.product', 'variant.attributeValues.attribute'])
            ->get();

        if ($cartItems->isEmpty()) {
            Log::warning('Không tìm thấy sản phẩm hợp lệ để đặt hàng cho user: ' . $user->id);
            return response()->json(['message' => 'Không có sản phẩm hợp lệ để đặt hàng.'], Response::HTTP_BAD_REQUEST);
        }
        // -----------------------------------------------------------

        Log::info('Các sản phẩm được chọn để đặt hàng:', ['item_count' => $cartItems->count(), 'item_ids' => $cartItems->pluck('id')]);


        $addressData = $this->resolveAddressData($user, $validated);
        if ($addressData instanceof \Illuminate\Http\JsonResponse) {
            Log::warning('Lỗi địa chỉ từ resolveAddressData cho user ' . $user->id . ': ' . $addressData->getContent());
            return $addressData;
        }

        Log::info('Dữ liệu địa chỉ đã được giải quyết:', $addressData);

        DB::beginTransaction();

        try {
            $totalItemsPrice = 0;
            foreach ($cartItems as $item) {
                $variantId = $item->variant_id ?? ($item->variant->id ?? null);

                if (!$variantId) {
                    DB::rollBack();
                    Log::error('Không tìm thấy variant_id trong cart item.', [
                        'user_id' => $user->id,
                        'cart_item' => $item,
                    ]);
                    return response()->json(['message' => 'Không thể xác định sản phẩm.'], 400);
                }

                $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();

                if (!$variant || $variant->stock < $item->quantity || $variant->status === 'unavailable') {
                    DB::rollBack();
                    Log::warning('Sản phẩm không đủ tồn kho/không sẵn: ', [
                        'user_id' => $user->id,
                        'cart_item_id' => $item->id,
                        'variant_id' => $variant?->id ?? 'N/A',
                        'requested_quantity' => $item->quantity,
                        'available_stock' => $variant?->stock ?? 'N/A',
                        'status' => $variant?->status ?? 'N/A'
                    ]);
                    return response()->json(['message' => 'Sản phẩm "' . ($variant->product->name ?? 'không xác định') . '" không đủ tồn kho hoặc không có sẵn.'], 400);
                }

                $totalItemsPrice += $variant->price * $item->quantity;
            }

            Log::info('Tổng tiền sản phẩm (totalItemsPrice): ' . $totalItemsPrice);

            $shippingMethod = ShippingMethod::where('id', $validated['shipping_method_id'])->where('is_active', true)->first();
            if (!$shippingMethod) {
                DB::rollBack();
                Log::warning('Phương thức vận chuyển không hợp lệ hoặc không hoạt động: ' . ($validated['shipping_method_id'] ?? 'N/A'));
                return response()->json(['message' => 'Phương thức vận chuyển đã chọn không hợp lệ hoặc không hoạt động.'], Response::HTTP_BAD_REQUEST);
            }

            $shippingFee = $shippingMethod->price;
            Log::info('Phí vận chuyển: ' . $shippingFee . ' từ phương thức: ' . $shippingMethod->name);

            $coupon = null;
            $couponDiscount = 0;
            $finalTotal = $totalItemsPrice;

            if (!empty($validated['coupon_code'])) {
                try {
                    $couponResult = $this->applyCoupon($validated['coupon_code'], $totalItemsPrice, $user);
                    $coupon = $couponResult['coupon'];
                    $couponDiscount = $couponResult['discount'];
                    $finalTotal = max(0, $totalItemsPrice - $couponDiscount);
                    Log::info('Voucher đã áp dụng:', ['code' => $validated['coupon_code'], 'discount' => $couponDiscount]);
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::warning('Lỗi khi áp dụng voucher cho user ' . $user->id . ': ' . $e->getMessage());
                    return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
                }
            }

            $finalTotal += $shippingFee;
            Log::info('Tổng tiền cuối cùng trước khi tạo Order (finalTotal): ' . $finalTotal);



            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal,
                'status' => Order::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'shipping_fee' => $shippingFee,
                'shipping_method_id' => $shippingMethod->id,
                'discount_amount' => $couponDiscount,
            ]);

            Log::info('Đã tạo đơn hàng:', ['order_id' => $order->id, 'total_price' => $order->total_price]);

            OrderAddress::create(array_merge(['order_id' => $order->id], $addressData));

            Log::info('Đã tạo địa chỉ cho đơn hàng:', ['order_id' => $order->id, 'address' => $addressData]);


            foreach ($cartItems as $item) { // Vòng lặp bây giờ chỉ xử lý các item được chọn
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

                // LOG 13: Cập nhật tồn kho cho mỗi item

                Log::info('Đã xử lý item đơn hàng và cập nhật tồn kho:', [
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'quantity' => $item->quantity,
                    'new_stock' => $variant->stock
                ]);
            }

            // --- Thay đổi lớn tiếp theo: Xóa các CartItem đã mua ---
            CartItem::whereIn('id', $cartItemIds)->delete();
            Log::info('Đã xóa các sản phẩm khỏi giỏ hàng sau khi đặt hàng thành công:', ['cart_item_ids' => $cartItemIds]);
            // ----------------------------------------------------

            // Tăng used_count của coupon nếu được sử dụng
            if ($coupon) {
                $coupon->increment('used_count');
                Log::info('Used count của coupon đã tăng: ' . $coupon->code);
            }

            $paymentResult = $this->processPayment($order, $validated['payment_method'], $finalTotal);
            Log::info('Kết quả xử lý thanh toán:', $paymentResult);

            if ($paymentResult['status'] === Payment::PAYMENT_STATUS_PAID || $paymentResult['status'] === 'completed') {
                $order->update(['status' => Order::STATUS_PENDING]);
            } elseif ($paymentResult['status'] === Payment::PAYMENT_STATUS_PENDING) {
                $order->update(['status' => Order::STATUS_PENDING]);
            } elseif ($paymentResult['status'] === 'redirect') {
                $order->update(['status' => Order::STATUS_PENDING_PAYMENT]);
            } else {
                $order->update(['status' => Order::STATUS_PAYMENT_FAILED]);
                DB::rollBack();
                Log::error('Lỗi trong quá trình xử lý thanh toán cho order ' . $order->id . ': ' . ($paymentResult['message'] ?? 'Không rõ lỗi.'));
                return response()->json(['message' => $paymentResult['message']], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            try {
                // Reload order kèm các quan hệ cần cho email
                $order = Order::with([
                    'user',
                    'orderAddress.province',
                    'orderAddress.district',
                    'orderAddress.ward',
                    'payment'
                ])->find($order->id);

                // Gửi email xác nhận
                Mail::to($order->user->email)->send(new OrderPlacedMail($order));
                Log::info('Đã gửi email xác nhận đơn hàng đến: ' . $order->user->email);
            } catch (\Exception $e) {
                Log::error('Không thể gửi email xác nhận đơn hàng: ' . $e->getMessage());
            }


            Log::info('Transaction đặt hàng thành công cho user: ' . $user->id . ' order: ' . $order->id);

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->id,
                'payment_info' => $paymentResult,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tổng quát khi đặt hàng từ giỏ cho user ' . $user->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
            'payment_method' => 'required|string|in:' . implode(',', $allowedPaymentMethods),
            'shipping_method_id' => 'required|exists:shipping_methods,id,is_active,1', // Validate selected shipping method
        ]);

        $addressData = $this->resolveAddressData($user, $validated);
        if ($addressData instanceof \Illuminate\Http\JsonResponse) {
            return $addressData; // Return error response from resolveAddressData
        }

        $variant = ProductVariant::with(['product', 'attributeValues.attribute'])
            ->where('id', $validated['product_variant_id'])
            ->lockForUpdate() // 💥 Quan trọng để chống oversell
            ->first();

        if (!$variant || $variant->stock < $validated['quantity'] || $variant->status === 'unavailable') {
            DB::rollBack(); // 🛑 Huỷ transaction nếu hết hàng
            return response()->json(['message' => 'Sản phẩm không đủ tồn kho hoặc không có sẵn.'], Response::HTTP_BAD_REQUEST);
        }

        $totalItemsPrice = $variant->price * $validated['quantity'];

        // Fetch the selected shipping method
        $shippingMethod = ShippingMethod::where('id', $validated['shipping_method_id'])
            ->where('is_active', true)
            ->first();

        DB::beginTransaction();
        if (!$shippingMethod) {
            return response()->json(['message' => 'Phương thức vận chuyển đã chọn không hợp lệ hoặc không hoạt động.'], Response::HTTP_BAD_REQUEST);
        }
        $shippingFee = $shippingMethod->price; // Use price from selected shipping method

        $coupon = null;
        $couponDiscount = 0;
        $finalTotal = $totalItemsPrice; // Start with item price

        if (!empty($validated['coupon_code'])) {
            try {
                $couponResult = $this->applyCoupon($validated['coupon_code'], $totalItemsPrice, $user);
                $coupon = $couponResult['coupon'];
                $couponDiscount = $couponResult['discount'];
                $finalTotal = max(0, $totalItemsPrice - $couponDiscount);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        $finalTotal += $shippingFee; // Add shipping fee to the total


        try {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $finalTotal,
                'status' => Order::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'payment_method' => $validated['payment_method'],
                'shipping_fee' => $shippingFee,
                'shipping_method_id' => $shippingMethod->id, // Store shipping method ID
                'discount_amount' => $couponDiscount,
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

            //lưu vào inventory_logs
            // InventoryLog::create([
            //     'product_variant_id' => $variant->id,
            //     'user_id' => $user->id,
            //     'warehouse_id' => null, // nếu chưa dùng đa kho
            //     'type' => 'export', // xuất kho vì người dùng mua hàng
            //     'quantity_change' => -$validated['quantity'],
            //     'note' => 'Mua ngay - Đơn hàng ID #' . $order->id,
            // ]);


            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Gọi hàm xử lý thanh toán
            $paymentResult = $this->processPayment($order, $validated['payment_method'], $finalTotal);

            // Cập nhật trạng thái đơn hàng dựa trên kết quả thanh toán
            if ($paymentResult['status'] === Payment::PAYMENT_STATUS_PAID || $paymentResult['status'] === 'completed') {
                $order->update(['status' => Order::STATUS_PENDING]);
            } elseif ($paymentResult['status'] === Payment::PAYMENT_STATUS_PENDING) {
                $order->update(['status' => Order::STATUS_PENDING]);
            } elseif ($paymentResult['status'] === 'redirect') {
                $order->update(['status' => Order::STATUS_PENDING_PAYMENT]);
            } else {
                $order->update(['status' => Order::STATUS_PAYMENT_FAILED]);
                DB::rollBack();
                return response()->json(['message' => $paymentResult['message']], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            // try {
            //     Mail::to($user->email)->send(new OrderPlacedMail($order));
            //     Log::info('Đã gửi email xác nhận đơn hàng đến: ' . $user->email);
            // } catch (\Exception $e) {
            //     Log::error('Không thể gửi email xác nhận đơn hàng: ' . $e->getMessage());
            // }

            try {
                // Reload order kèm các quan hệ cần cho email
                $order = Order::with([
                    'user',
                    'orderAddress.province',
                    'orderAddress.district',
                    'orderAddress.ward',
                    'payment'
                ])->find($order->id);

                // Gửi email xác nhận
                Mail::to($order->user->email)->send(new OrderPlacedMail($order));
                Log::info('Đã gửi email xác nhận đơn hàng đến: ' . $order->user->email);
            } catch (\Exception $e) {
                Log::error('Không thể gửi email xác nhận đơn hàng: ' . $e->getMessage());
            }

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
        Log::info('processPayment: Bắt đầu xử lý thanh toán.', [
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'amount' => $amount
        ]);

        // Tạo bản ghi Payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'payment_status' => Payment::PAYMENT_STATUS_PENDING, // Luôn pending ban đầu
            'payment_details' => [], // Mảng rỗng ban đầu để lưu chi tiết sau
        ]);

        Log::info('processPayment: Đã tạo bản ghi Payment.', ['payment_id' => $payment->id]);

        try {
            switch ($paymentMethod) {
                case 'cash':
                    $payment->update([
                        'payment_status' => Payment::PAYMENT_STATUS_PENDING, // Vẫn pending cho COD
                        'paid_at' => null, // Chưa thanh toán thực tế
                        'transaction_id' => 'COD_' . $order->id . '_' . now()->format('YmdHis'), // ID giao dịch nội bộ
                        'payment_details' => ['message' => 'Thanh toán khi nhận hàng.']
                    ]);
                    Log::info('processPayment: Xử lý COD hoàn tất.', ['payment_id' => $payment->id, 'status' => $payment->payment_status]);
                    return [
                        'status' => 'completed', // Coi như bước khởi tạo hoàn tất, không cần redirect
                        'message' => 'Thanh toán COD đã được ghi nhận. Đơn hàng sẽ được xử lý.',
                        'payment_status' => $payment->payment_status,
                        'payment_id' => $payment->id,
                        'method' => 'cash'
                    ];

                case 'momo':
                    Log::info('processPayment: Chuẩn bị gọi MoMo Payment Gateway.', ['payment_id' => $payment->id]);
                    // Call the MomoPaymentService
                    $momoResponse = $this->momoService->createPayment($amount, $order->id, $payment->id, "Thanh toan don hang #{$order->id}");

                    if ($momoResponse['status'] === 'success') {
                        // Update payment record with MoMo details
                        $payment->update([
                            'transaction_id' => $momoResponse['transId'] ?? null, // MoMo transId if available from initial request
                            'payment_details' => $momoResponse['rawResponse'] // Store full raw response
                        ]);
                        Log::info('processPayment: Nhận được payUrl từ MoMo.', ['payUrl' => $momoResponse['payUrl']]);

                        return [
                            'status' => 'redirect', // Indicate that a redirect is needed
                            'message' => 'Chuyển hướng đến cổng thanh toán MoMo.',
                            'payment_status' => Payment::PAYMENT_STATUS_PENDING, // Still pending until IPN confirms
                            'payment_id' => $payment->id,
                            'payUrl' => $momoResponse['payUrl'],
                            'method' => 'momo'
                        ];
                    } else {
                        // MoMo initial request failed
                        $payment->update([
                            'payment_status' => Payment::PAYMENT_STATUS_FAILED,
                            'payment_details' => $momoResponse['rawResponse'] ?? ['error' => $momoResponse['message']]
                        ]);
                        Log::error('processPayment: Lỗi khi tạo yêu cầu MoMo.', ['response' => $momoResponse]);
                        throw new Exception($momoResponse['message'] ?? 'Lỗi không xác định khi tạo yêu cầu thanh toán MoMo.');
                    }

                case 'vnpay':
                    Log::info('processPayment: Chuẩn bị gọi VNPay Payment Gateway.', ['payment_id' => $payment->id]);
                    // Call the VnPayPaymentService
                    $vnpayResponse = $this->vnpayService->createPayment($amount, $order->id, $payment->id, request()->ip(), "Thanh toan don hang #{$order->id}");

                    if ($vnpayResponse['status'] === 'success') {
                        // Update payment record with VNPay details (if any immediate transaction ID is returned)
                        $payment->update([
                            'payment_details' => $vnpayResponse['rawResponse'] // Store full raw response
                        ]);
                        Log::info('processPayment: Nhận được payUrl từ VNPay.', ['payUrl' => $vnpayResponse['payUrl']]);

                        return [
                            'status' => 'redirect', // Indicate that a redirect is needed
                            'message' => 'Chuyển hướng đến cổng thanh toán VNPay.',
                            'payment_status' => Payment::PAYMENT_STATUS_PENDING, // Still pending until IPN confirms
                            'payment_id' => $payment->id,
                            'payUrl' => $vnpayResponse['payUrl'],
                            'method' => 'vnpay'
                        ];
                    } else {
                        // VNPay initial request failed
                        $payment->update([
                            'payment_status' => Payment::PAYMENT_STATUS_FAILED,
                            'payment_details' => $vnpayResponse['rawResponse'] ?? ['error' => $vnpayResponse['message']]
                        ]);
                        Log::error('processPayment: Lỗi khi tạo yêu cầu VNPay.', ['response' => $vnpayResponse]);
                        throw new Exception($vnpayResponse['message'] ?? 'Lỗi không xác định khi tạo yêu cầu thanh toán VNPay.');
                    }

                default:
                    Log::error('processPayment: Phương thức thanh toán không hợp lệ.', ['method' => $paymentMethod]);
                    throw new Exception('Phương thức thanh toán không hợp lệ.');
            }
        } catch (Exception $e) {
            // Log the error and mark payment as failed if an exception occurs
            Log::error("Lỗi trong processPayment cho Order ID {$order->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $payment->update([
                'payment_status' => Payment::PAYMENT_STATUS_FAILED,
                'payment_details' => array_merge($payment->payment_details ?? [], ['error_message' => $e->getMessage()])
            ]);
            // Re-throw the exception to be caught by the main transaction block in placeOrder/buyNow
            throw $e;
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
            if (
                !($validatedData['recipient_name'] && $validatedData['phone_number'] &&
                    $validatedData['address_line'] && $validatedData['ward'] &&
                    $validatedData['district'] && $validatedData['province'])
            ) {
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
