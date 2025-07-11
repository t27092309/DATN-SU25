<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ->orderBy('created_at', 'desc')
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
                'payment'
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

            return [
                'id' => $item->id,
                'product_name' => $item->productVariant->product->name ?? 'N/A',
                'product_image' => $item->productVariant->product->image ?? 'https://via.placeholder.com/64',
                'variant_name' => $displayVariantName, // Tên biến thể đã được định dạng
                'quantity' => $item->quantity,
                'price_each' => $item->price_each,
                'subtotal' => $item->price_each * $item->quantity,
            ];
        });

        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'total_price' => $order->total_price,
            'status' => $order->status,
            'notes' => $order->notes,
            'coupon_id' => $order->coupon_id,
            'payment_method' => $order->payment_method,
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
            'payment_info' => $order->payment ? [
                'payment_method' => $order->payment->payment_method,
                'amount' => $order->payment->amount,
                'payment_status' => $order->payment->payment_status,
                'paid_at' => $order->payment->paid_at ? $order->payment->paid_at->toDateTimeString() : null,
            ] : null,
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

    public function markAsDelivered(Order $order) // Sử dụng Route Model Binding
    {
        $user = Auth::user();

        // Kiểm tra xem đơn hàng có thuộc về người dùng hiện tại không
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền truy cập đơn hàng này.'], Response::HTTP_FORBIDDEN);
        }

        // Kiểm tra trạng thái hiện tại của đơn hàng
        // Chỉ cho phép đánh dấu 'delivered' nếu trạng thái hiện tại là 'shipped' (Đang giao hàng)
        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Đơn hàng không ở trạng thái "Đang giao hàng" để xác nhận đã nhận.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $order->status = 'completed';
            $order->delivered_at = now(); // Ghi lại thời điểm giao hàng
            $order->save();

            return response()->json(['message' => 'Đơn hàng đã được đánh dấu là Đã giao hàng.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Ghi log lỗi để dễ dàng debug
            \Log::error('Lỗi khi đánh dấu đơn hàng đã giao: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => $user->id]);
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
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Chỉ có thể hủy các đơn hàng đang ở trạng thái "Chờ xác nhận".'], Response::HTTP_BAD_REQUEST);
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
                    $variant->increment('stock_quantity', $item->quantity);
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
}
