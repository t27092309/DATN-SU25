<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\LookupOrderRequest;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{

    //  GET /api/admin/orders
    //  Lấy danh sách đơn hàng có phân trang, lọc theo trạng thái, tìm kiếm user/name/id

    public function index(Request $request)
    {
        // Tạo query builder để lấy danh sách đơn hàng
        $query = Order::with(['user', 'orderItems.productVariant', 'orderAddress', 'payments'])
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

        // Tạo một query mới để đếm số lượng đơn hàng theo từng trạng thái
        // Việc này cần thiết để đảm bảo việc đếm không bị ảnh hưởng bởi pagination.
        // Bạn có thể cache kết quả này để tối ưu hiệu suất
        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
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
}
