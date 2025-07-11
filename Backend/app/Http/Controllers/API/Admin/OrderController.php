<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
class OrderController extends Controller
{
    
    //  GET /api/admin/orders
    //  Lấy danh sách đơn hàng có phân trang, lọc theo trạng thái, tìm kiếm user/name/id
     
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.productVariant', 'orderAddress', 'payments'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('id', $request->search)
                    ->orWhereHas('user', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->latest();

        return OrderResource::collection($query->paginate(15));
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

   
     //PUT /api/admin/orders/{order}/status
     //Cập nhật trạng thái đơn hàng

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::ALL_STATUSES),
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
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
