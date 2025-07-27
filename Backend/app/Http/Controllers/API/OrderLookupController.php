<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LookupOrderRequest;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
class OrderLookupController extends Controller
{
    //POST: http://localhost:8000/api/orders/lookup
    public function lookupByPhone(LookupOrderRequest $request)
{
    $phone = $request->input('phone');
    

    $orders = Order::whereHas('orderAddress', function ($query) use ($phone) {
        $query->where('phone_number', $phone);
    })
        ->with([
            'orderItems.product',
            'orderItems.productVariant',
            'orderAddress'
        ])
        ->latest()
        ->paginate(10);

    if ($orders->total() === 0) {
        return response()->json([
            'message' => 'Không tìm thấy đơn hàng nào với số điện thoại này.',
        ], 404);
    }

    $data = $orders->getCollection()->map(function ($order) {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'customer_name' => $order->orderAddress->recipient_name ?? null,
            'customer_phone' => $order->orderAddress->phone_number ?? null,
            'total_amount' => $order->total_price,
            'order_status' => $order->status,
            'payment_status' => $order->payment_status ?? 'unpaid',
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'Sản phẩm đã xoá',
                    'quantity' => $item->quantity,
                    'price' => $item->price_each,
                    'variant' => $item->productVariant ? [
                        'name' => $item->productVariant->name
                    ] : null,
                ];
            }),
        ];
    });

    return response()->json([
        'message' => 'Đã tìm thấy đơn hàng thành công!',
        'data' => $data,
        'pagination' => [
            'total' => $orders->total(),
            'per_page' => $orders->perPage(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ]
    ]);
}
}
