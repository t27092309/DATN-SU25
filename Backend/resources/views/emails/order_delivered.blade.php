<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Giao hàng thành công</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; background-color: #fff; padding: 30px; margin: 0 auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2 style="color: #28a745;">Đơn hàng #{{ $order->id }} đã giao thành công!</h2>

        <p style="font-size: 16px;">Xin chào {{ $order->user->name ?? 'Khách hàng' }},</p>

        <p>Chúng tôi xin thông báo đơn hàng của bạn đã được giao thành công đến địa chỉ đã cung cấp.</p>

        <p><strong>Thông tin đơn hàng:</strong></p>
        <ul>
            <li><strong>Mã đơn hàng:</strong> #{{ $order->id }}</li>
            <li><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</li>
            <li><strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }}</li>
            <li><strong>Tổng tiền:</strong> {{ number_format($order->total_price, 0, ',', '.') }} VND</li>
        </ul>

        @if ($order->shippingAddress)
        <p><strong>Giao đến:</strong><br>
            {{ $order->shippingAddress->name }} - {{ $order->shippingAddress->phone }}<br>
            {{ $order->shippingAddress->address }}<br>
            {{ $order->shippingAddress->ward->name ?? '' }},
            {{ $order->shippingAddress->district->name ?? '' }},
            {{ $order->shippingAddress->province->name ?? '' }}
        </p>
        @endif

        <hr>
        <p style="font-size: 14px; color: #777;">Nếu bạn hài lòng với sản phẩm, hãy đánh giá giúp chúng tôi nhé!</p>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Mua thêm sản phẩm khác</a>
        </p>

        <p style="margin-top: 30px;">Cảm ơn bạn,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
