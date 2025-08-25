<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderItem;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\LookupOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{

    //  GET /api/admin/orders
    //  Lấy danh sách đơn hàng có phân trang, lọc theo trạng thái, tìm kiếm user/name/id

    public function index(Request $request)
    {
        // Tạo query builder để lấy danh sách đơn hàng
        $query = Order::with(['user', 'orderItems.productVariant', 'orderAddress', 'payments', 'returnRequest.processor'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('id', $request->search)
                    ->orWhereHas('user', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('user', function ($sub) use ($request) {
                        $sub->where('phone_number', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('orderAddress', function ($sub) use ($request) {
                        $sub->where('phone_number', 'like', '%' . $request->search . '%');
                    });
            })
            ->orderBy('updated_at', 'desc');

        // Lấy danh sách đơn hàng đã được phân trang
        $orders = $query->paginate(15);

        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'return_requested' => Order::where('status', 'return_requested')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
        ];

        // Trả về dữ liệu JSON bao gồm cả danh sách đơn hàng và số lượng đếm
        return response()->json([
            'data' => OrderResource::collection($orders)->resolve(),
            'counts' => $counts,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'from' => $orders->firstItem(),
                'last_page' => $orders->lastPage(),
                'links' => $orders->linkCollection()->toArray(),
                'path' => $orders->path(),
                'per_page' => $orders->perPage(),
                'to' => $orders->lastItem(),
                'total' => $orders->total(),
            ],
            'links' => [
                'first' => $orders->url(1),
                'last' => $orders->url($orders->lastPage()),
                'prev' => $orders->previousPageUrl(),
                'next' => $orders->nextPageUrl(),
            ],
        ]);
    }


    // GET /api/admin/orders/{order}
    // Xem chi tiết đơn hàng
    public function show(Order $order)
    {
        $order->load([
            'user',
            'orderItems.productVariant',
            'orderAddress',
            'payments',
            'returnRequest.processor'
        ]);

        return new OrderResource($order);
    }


    //PATCH /api/admin/orders/{order}/status
    //Cập nhật trạng thái đơn hàng

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::ALL_STATUSES),
        ]);

        if (
            $request->status === Order::STATUS_CANCELLED &&
            in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đơn hàng khi đang giao hoặc đã giao hàng.',
            ], 400);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Trạng thái đơn hàng đã được cập nhật.',
            'data' => new OrderResource($order->fresh(['user', 'orderItems.productVariant', 'orderAddress', 'payments']))
        ]);
    }


    // PUT /api/admin/orders/{order}/note
    // Cập nhật ghi chú đơn hàng

    public function updateNote(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $order->notes = $request->notes;
        $order->save();

        return response()->json([
            'message' => 'Ghi chú đã được cập nhật.',
            'notes' => $order->notes,
        ]);
    }


    // GET /api/admin/orders/{order}/payments
    // Lấy danh sách thanh toán của đơn hàng

    public function getPayments(Order $order)
    {
        $order->load('payments');
        return PaymentResource::collection($order->payments);
    }

    /**
     * Lấy danh sách các yêu cầu hoàn trả đang chờ xử lý.
     * Route: GET /api/admin/orders/returns/requested
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnRequests(Request $request)
    {
        $returns = OrderReturn::where('status', 'requested')
            ->with(['order.user', 'order.orderItems.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Bạn có thể tạo một OrderReturnResource để định dạng dữ liệu trả về cho gọn gàng hơn
        return response()->json([
            'data' => $returns->toArray(), // Tạm thời dùng toArray()
            'meta' => [
                'total' => $returns->total(),
                'per_page' => $returns->perPage(),
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
            ]
        ]);
    }

    /**
     * Duyệt yêu cầu hoàn trả.
     * Route: POST /api/admin/orders/{order}/returns/approve
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveReturn(Order $order): JsonResponse
    {
        $returnRequest = $order->returnRequest;
        if (!$returnRequest || $returnRequest->status !== 'requested') {
            return response()->json([
                'message' => 'Không tìm thấy yêu cầu hoàn trả đang chờ xử lý cho đơn hàng này.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $returnRequest->status = 'approved';
            $returnRequest->processed_by = Auth::id();
            $returnRequest->processed_at = now();
            $returnRequest->save();

            $order->status = 'return_requested';
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Đã duyệt yêu cầu hoàn trả thành công.',
                'data' => [
                    'order' => new OrderResource($order->fresh(['returnRequest.processor'])),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi duyệt yêu cầu hoàn trả: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id()]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xử lý. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Từ chối yêu cầu hoàn trả.
     * Route: POST /api/admin/orders/{order}/returns/reject
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectReturn(Order $order, Request $request): JsonResponse
    {
        $returnRequest = $order->returnRequest;
        if (!$returnRequest || $returnRequest->status !== 'requested') {
            return response()->json([
                'message' => 'Không tìm thấy yêu cầu hoàn trả đang chờ xử lý cho đơn hàng này.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $returnRequest->status = 'rejected';
            $returnRequest->notes = $request->input('notes', 'Yêu cầu bị từ chối.');
            $returnRequest->processed_by = Auth::id();
            $returnRequest->processed_at = now();
            $returnRequest->save();

            $order->status = 'rejected';
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Yêu cầu hoàn trả đã bị từ chối.',
                'data' => [
                    'order' => new OrderResource($order->fresh(['returnRequest.processor'])),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi từ chối yêu cầu hoàn trả: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id()]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xử lý. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Đánh dấu đã nhận hàng hoàn trả từ khách hàng.
     * Route: POST /api/admin/orders/{order}/returns/received
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Xác nhận đã nhận hàng hoàn trả và cập nhật tồn kho.
     * Route: POST /api/admin/orders/{order}/returns/received
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function markAsReturned(Order $order): JsonResponse
    {
        $returnRequest = $order->returnRequest;

        // if ($order->status !== 'return_requested' || !$returnRequest || $returnRequest->status !== 'approved') {
        //     return response()->json([
        //         'message' => 'Đơn hàng không ở trạng thái "Đã duyệt yêu cầu hoàn trả" để có thể nhận hàng.'
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        if (!$returnRequest || $returnRequest->status !== 'approved') {
            return response()->json([
                'message' => 'Yêu cầu hoàn trả chưa được duyệt để có thể nhận hàng.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Chỉ cập nhật trạng thái của yêu cầu hoàn trả
            $returnRequest->status = 'returned';
            $returnRequest->processed_by = Auth::id();
            $returnRequest->processed_at = now();
            $returnRequest->save();

            // Cập nhật trạng thái đơn hàng chính
            $order->status = 'return_requested'; // hoặc ''
            $order->save();

            // Cập nhật tồn kho sản phẩm
            foreach ($order->orderItems as $item) {
                $variant = $item->productVariant;
                if ($variant) {
                    $variant->increment('stock', $item->quantity);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Đã xác nhận nhận hàng hoàn trả và cập nhật tồn kho.',
                'data' => [
                    'order' => new OrderResource($order->fresh(['returnRequest.processor'])),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xác nhận hàng hoàn trả: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id()]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xử lý. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Hoàn tiền cho khách hàng.
     * Route: POST /api/admin/orders/{order}/returns/refund
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function refundOrder(Order $order): JsonResponse
    {
        $returnRequest = $order->returnRequest;

        // if ($order->status !== 'return_requested' || !$returnRequest || $returnRequest->status !== 'returned') {
        //     return response()->json([
        //         'message' => 'Đơn hàng không ở trạng thái "Đã nhận hàng hoàn trả" để có thể hoàn tiền.'
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        if (!$returnRequest || $returnRequest->status !== 'returned') {
            return response()->json([
                'message' => 'Chưa nhận hàng hoàn trả để hoàn tiền.'
            ], Response::HTTP_BAD_REQUEST);
        }


        // TODO: Thêm logic gọi API cổng thanh toán để thực hiện hoàn tiền ở đây.

        try {
            DB::beginTransaction();

            $returnRequest->status = 'refunded';
            $returnRequest->processed_by = Auth::id();
            $returnRequest->processed_at = now();
            $returnRequest->save();

            // Cập nhật trạng thái đơn hàng chính thành 'refunded'
            $order->status = 'refunded';
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Đã hoàn tiền thành công cho khách hàng.',
                'data' => [
                    'order' => new OrderResource($order->fresh(['returnRequest.processor'])),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hoàn tiền cho đơn hàng: ' . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id()]);
            return response()->json([
                'message' => 'Có lỗi xảy ra trong quá trình hoàn tiền. Vui lòng thử lại.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
