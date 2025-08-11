<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\Cart;
use App\Models\CartItem;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderDeliveredMail;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Lấy danh sách tất cả các đơn hàng của người dùng đã xác thực.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Bắt đầu truy vấn
        $ordersQuery = Order::where('user_id', $user->id);

        // --- Bổ sung logic lọc theo trạng thái ---
        $status = $request->query('status'); // Lấy tham số 'status' từ query string
        if ($status && $status !== 'all') { // Nếu có trạng thái và không phải 'all'
            $ordersQuery->where('status', $status);
        }
        // --- Kết thúc logic lọc theo trạng thái ---

        // Bổ sung logic tìm kiếm (nếu bạn muốn tìm kiếm theo tên sản phẩm hoặc ID đơn hàng)
        $search = $request->query('search');
        if ($search) {
            $ordersQuery->where(function ($query) use ($search) {
                // Tìm kiếm theo ID đơn hàng (chuyển sang số nếu có thể)
                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }
                // Tìm kiếm theo tên sản phẩm trong OrderItems
                $query->orWhereHas('orderItems.productVariant.product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }


        // Eager load các mối quan hệ cần thiết để giảm số lượng truy vấn DB
        $orders = $ordersQuery->with([
            'orderItems.productVariant.product',
            'orderAddress',
            'payments'
        ])
            ->orderBy('updated_at', 'desc')
            ->paginate(10); // Phân trang cho các đơn hàng

        // Format dữ liệu nếu cần thiết (ví dụ: định dạng tiền tệ)
        $formattedOrders = $orders->getCollection()->map(function ($order) {
            return $this->formatOrderData($order);
        });

        return response()->json([
            'message' => 'Lấy danh sách đơn hàng thành công.',
            'orders' => $formattedOrders,
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Lấy chi tiết của một đơn hàng cụ thể của người dùng đã xác thực.
     *
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($orderId)
    {
        $user = Auth::user();

        // Tìm đơn hàng theo ID và đảm bảo nó thuộc về người dùng hiện tại
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with([
                'orderItems.productVariant.product',
                'orderItems.productVariant.attributeValues.attribute', // Tải chi tiết thuộc tính
                'orderAddress',
                'primaryPayment',
                'payments'
            ])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Lấy chi tiết đơn hàng thành công.',
            'order' => $this->formatOrderData($order)
        ], Response::HTTP_OK);
    }

    /**
     * Hàm hỗ trợ để định dạng dữ liệu đơn hàng cho response.
     *
     * @param Order $order
     * @return array
     */
    private function formatOrderData(Order $order): array
    {
        $items = $order->orderItems->map(function ($item) {
            // Reconstruct variant name with attributes
            $variantNameParts = [];
            if ($item->productVariant && $item->productVariant->attributeValues) {
                foreach ($item->productVariant->attributeValues as $attrValue) {
                    if ($attrValue->attribute && $attrValue->value) {
                        $variantNameParts[] = $attrValue->attribute->name . ': ' . $attrValue->value;
                    }
                }
            }
            $displayVariantName = !empty($variantNameParts) ? implode(' / ', $variantNameParts) : $item->variant_name;

            $productImage = 'https://via.placeholder.com/64'; // Default placeholder
            if ($item->productVariant && $item->productVariant->product && $item->productVariant->product->image) {
                $productImage = asset('storage/' . $item->productVariant->product->image);
            }
            $slug = null;
            if ($item->productVariant && $item->productVariant->product) {
                $product = $item->productVariant->product;
                $slug = $product->slug;
            };
            return [
                'id' => $item->id,
                'product_name' => $item->productVariant->product->name ?? 'N/A',
                'product_image' => $productImage, // Tên biến thể đã được định dạng
                'slug' => $slug,
                'variant_name' => $displayVariantName,
                'quantity' => $item->quantity,
                'price_each' => (float) $item->price_each,
                'subtotal' => (float) $item->price_each * $item->quantity,
            ];
        });

        $primaryPaymentInfo = null;
        if ($order->primaryPayment) {
            $primaryPaymentInfo = [
                'payment_method' => $order->primaryPayment->payment_method,
                'amount' => (float) $order->primaryPayment->amount,
                'payment_status' => $order->primaryPayment->payment_status, // Dùng 'status' thay vì 'payment_status'
                'paid_at' => $order->primaryPayment->paid_at ? $order->primaryPayment->paid_at->toDateTimeString() : null,
            ];
        }

        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'total_price' => $order->total_price,
            'status' => $order->status,
            'notes' => $order->notes,
            'coupon_id' => $order->coupon_id,
            'shipping_fee' => $order->shipping_fee,
            'created_at' => $order->created_at->toDateTimeString(),
            'updated_at' => $order->updated_at->toDateTimeString(),
            'order_address' => $order->orderAddress ? [
                'recipient_name' => $order->orderAddress->recipient_name,
                'phone_number' => $order->orderAddress->phone_number,
                'address_line' => $order->orderAddress->address_line,
                'ward' => $order->orderAddress->ward,
                'district' => $order->orderAddress->district,
                'province' => $order->orderAddress->province,
                'full_address' => implode(', ', array_filter([
                    $order->orderAddress->address_line,
                    $order->orderAddress->ward,
                    $order->orderAddress->district,
                    $order->orderAddress->province
                ]))
            ] : null,
            'payment_info' => $primaryPaymentInfo,
            'items' => $items,
        ];
    }

    public function getOrderCounts(Request $request)
    {
        $user = Auth::user();

        // Định nghĩa tất cả các trạng thái có thể có
        $statuses = ['all', 'pending', 'processing', 'shipped', 'completed', 'cancelled'];
        $counts = [];

        foreach ($statuses as $status) {
            $query = Order::where('user_id', $user->id);

            if ($status !== 'all') {
                $query->where('status', $status);
            }
            $counts[$status] = $query->count();
        }

        return response()->json([
            'counts' => $counts
        ]);
    }

    public function markAsDelivered(Order $order)
    {
        $user = Auth::user();
        $order->load('primaryPayment');

        Log::info('Bắt đầu xử lý yêu cầu xác nhận đã nhận hàng.', [
            'order_id_from_route' => $order->id,
            'user_id' => $user->id,
            'current_order_status' => $order->status
        ]);

        if ($order->user_id !== $user->id) {
            Log::warning('Lỗi phân quyền: User không sở hữu đơn hàng.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'owner_id' => $order->user_id
            ]);
            return response()->json(['message' => 'Bạn không có quyền truy cập đơn hàng này.'], Response::HTTP_FORBIDDEN);
        }

        if ($order->status !== 'shipped') {
            Log::warning('Lỗi trạng thái đơn hàng không hợp lệ.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'current_status' => $order->status,
                'expected_status' => 'shipped'
            ]);
            return response()->json(['message' => 'Đơn hàng không ở trạng thái "Đang giao hàng" để xác nhận đã nhận.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::transaction(function () use ($order) {
                // Cập nhật trạng thái đơn hàng
                $order->status = 'delivered';
                $order->delivered_at = now();
                $order->save();

                // Tìm và cập nhật bản ghi thanh toán chính
                $payment = $order->primaryPayment;
                if ($payment) {
                    // Sửa lại dòng này để sử dụng tên cột cũ: payment_status
                    $payment->payment_status = 'paid';
                    $payment->paid_at = now();
                    $payment->save();
                }
            });

            // Gửi mail thông báo đã giao hàng thành công
            try {
                $order = Order::with([
                    'user',
                    'orderAddress.province',
                    'orderAddress.district',
                    'orderAddress.ward',
                    'payment'
                ])->find($order->id);
                Mail::to($user->email)->send(new OrderDeliveredMail($order));
                Log::info('Email giao hàng thành công đã được gửi.', ['order_id' => $order->id, 'user_email' => $user->email]);
            } catch (\Exception $e) {
                Log::error('Lỗi khi gửi mail giao hàng thành công.', [
                    'order_id' => $order->id,
                    'user_email' => $user->email,
                    'error_message' => $e->getMessage(),
                ]);
            }
            Log::info('Cập nhật trạng thái đơn hàng và thanh toán thành công.', [
                'order_id' => $order->id,
                'new_order_status' => $order->status,
                'new_payment_status' => $order->primaryPayment->payment_status ?? 'N/A',
            ]);
            return response()->json(['message' => 'Đơn hàng đã được đánh dấu là Đã giao hàng, mail xác nhận đã được gửi.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái đơn hàng hoặc thanh toán.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Không thể cập nhật trạng thái đơn hàng. Vui lòng thử lại.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Hủy một đơn hàng.
     * Chỉ người dùng sở hữu đơn hàng mới có thể thực hiện.
     * Chỉ cho phép hủy nếu đơn hàng ở trạng thái 'pending' (Chờ xác nhận).
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Order $order) // Sử dụng Route Model Binding
    {
        $user = Auth::user();

        // Kiểm tra xem đơn hàng có thuộc về người dùng hiện tại không
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền truy cập đơn hàng này.'], Response::HTTP_FORBIDDEN);
        }

        // Kiểm tra trạng thái hiện tại của đơn hàng
        // Chỉ cho phép hủy nếu trạng thái là 'pending' (chờ xác nhận)
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Chỉ có thể hủy các đơn hàng đang ở trạng thái "Chờ xác nhận" hoặc "Đang xử lý".'], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction(); // Bắt đầu Transaction

            $order->status = 'cancelled';
            $order->cancelled_at = now(); // Ghi lại thời điểm hủy
            $order->save();

            // --- Hoàn trả tồn kho sản phẩm ---
            // Lặp qua các sản phẩm trong đơn hàng và cộng lại số lượng vào tồn kho
            foreach ($order->orderItems as $item) {
                $variant = $item->productVariant;
                if ($variant) {
                    $variant->increment('stock', $item->quantity);
                    // Bạn có thể cân nhắc thêm log hoặc kiểm tra lỗi ở đây
                }
            }
            // --- Kết thúc hoàn trả tồn kho ---

            DB::commit(); // Hoàn thành Transaction

            return response()->json(['message' => 'Đơn hàng đã được hủy thành công.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác Transaction nếu có lỗi
            \Log::error('Lỗi khi hủy đơn hàng: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => $user->id]);
            return response()->json(['message' => 'Không thể hủy đơn hàng. Vui lòng thử lại.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mua lại một đơn hàng đã hoàn tất bằng cách thêm các sản phẩm vào giỏ hàng.
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Order $order)
    {
        $user = Auth::user();

        // 1. Kiểm tra xem đơn hàng có thuộc về người dùng hiện tại không
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập đơn hàng này.'
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Tìm giỏ hàng hiện tại của người dùng. Nếu chưa có, tạo mới.
        $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

        // 3. Tải các sản phẩm của đơn hàng cũ
        $order->load('orderItems.productVariant');

        try {
            DB::beginTransaction();

            // 4. Lặp qua các sản phẩm trong đơn hàng cũ và thêm vào bảng cart_items
            foreach ($order->orderItems as $item) {
                $productVariant = $item->productVariant;

                // Kiểm tra xem biến thể sản phẩm có tồn tại và còn hàng không
                if (!$productVariant || $productVariant->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Sản phẩm "' . $item->product_name . '" hiện không đủ số lượng trong kho.'
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Thêm hoặc cập nhật sản phẩm trong bảng cart_items
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $productVariant->id)
                    ->first();

                if ($cartItem) {
                    // Nếu sản phẩm đã có trong giỏ, cập nhật số lượng
                    $cartItem->quantity += $item->quantity;
                    $cartItem->save();
                } else {
                    // Nếu chưa có, tạo mới một bản ghi trong cart_items
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $productVariant->product_id, // Lấy product_id từ productVariant
                        'product_variant_id' => $productVariant->id,
                        'price' => $productVariant->price, // Giả sử bạn có giá trong product_variants
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Đã thêm các sản phẩm từ đơn hàng cũ vào giỏ hàng của bạn.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi mua lại đơn hàng: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => $user->id
            ]);
            return response()->json([
                'message' => 'Không thể mua lại đơn hàng. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function requestReturn(Request $request, Order $order)
    {
        $user = Auth::user();

        // 1. Xác thực người dùng và quyền sở hữu đơn hàng
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền gửi yêu cầu hoàn trả cho đơn hàng này.'
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Kiểm tra trạng thái đơn hàng và thời gian
        $maxReturnDays = 7;

        // **THAY ĐỔI 1: Cập nhật điều kiện kiểm tra trạng thái**
        // Đơn hàng phải ở trạng thái 'delivered' VÀ không có yêu cầu hoàn trả nào đang chờ xử lý
        if ($order->status !== 'delivered' || !$order->delivered_at || Carbon::parse($order->delivered_at)->diffInDays(now()) > $maxReturnDays) {
            return response()->json([
                'message' => 'Đơn hàng phải ở trạng thái "Đã giao" và thời gian yêu cầu không quá ' . $maxReturnDays . ' ngày kể từ ngày nhận hàng.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // **THAY ĐỔI 2: Xóa bỏ kiểm tra trùng lặp tại đây**
        // Chúng ta sẽ không cần kiểm tra này nữa vì trạng thái của đơn hàng đã được cập nhật
        // ngay sau khi gửi yêu cầu. Điều kiện ở bước 2 đã đủ để ngăn chặn gửi trùng lặp.

        // 3. Validate dữ liệu đầu vào từ request
        $request->validate([
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // 4. Tạo một bản ghi mới trong bảng 'order_returns'
            $orderReturn = new OrderReturn();
            $orderReturn->order_id = $order->id;
            $orderReturn->reason = $request->reason;
            $orderReturn->notes = $request->notes;
            $orderReturn->status = 'requested'; // Giả định có trường status trong bảng order_returns
            $orderReturn->save();

            // **THAY ĐỔI 3: Cập nhật trạng thái đơn hàng trong bảng 'orders'**
            // Quan trọng: Thay đổi status của đơn hàng chính
            $order->status = 'return_requested';
            $order->save();

            // 5. Trả về phản hồi thành công
            return response()->json([
                'message' => 'Yêu cầu hoàn trả của bạn đã được gửi thành công và đang chờ xét duyệt.'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo yêu cầu hoàn trả: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => $user->id]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi tạo yêu cầu hoàn trả. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
