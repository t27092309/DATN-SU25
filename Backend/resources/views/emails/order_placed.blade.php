<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Đặt hàng thành công</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f8f8; padding: 20px;">
    <div
        style="max-width: 600px; background-color: #ffffff; padding: 30px; margin: 0 auto; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2 style="color: #333;">Xin chào {{ $order->user->name ?? 'Khách hàng' }},</h2>

        <p style="font-size: 16px;">Cảm ơn bạn đã đặt hàng tại <strong>{{ config('app.name') }}</strong>!</p>

        <p>Thông tin đơn hàng của bạn:</p>
        <ul>
            <li><strong>Mã đơn hàng:</strong> #{{ $order->id }}</li>
            <li><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</li>
            <li><strong>Hình thức thanh toán:</strong> {{ strtoupper($order->payment_method) }}</li>
            <li><strong>Trạng thái:</strong> {{ ucfirst($order->status) }}</li>
        </ul>

        <p><strong>Tổng tiền:</strong>
            {{ number_format($order->final_amount ?? $order->total_price, 0, ',', '.') }} VND
        </p>

        @if ($order->shippingAddress)
            <p><strong>Địa chỉ giao hàng:</strong><br>
                {{ $order->shippingAddress->name }} - {{ $order->shippingAddress->phone }}<br>
                {{ $order->shippingAddress->address }}<br>
                {{ $order->shippingAddress->ward->name ?? '' }},
                {{ $order->shippingAddress->district->name ?? '' }},
                {{ $order->shippingAddress->province->name ?? '' }}
            </p>
        @endif

        <hr>
        <p style="font-size: 14px; color: #777;">Chúng tôi sẽ tiến hành xử lý đơn hàng và giao hàng sớm nhất.</p>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}"
                style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Truy
                cập cửa hàng</a>
        </p>

        <p style="margin-top: 30px;">Trân trọng,<br>Đội ngũ {{ config('app.name') }}</p>
    </div>
</body>

</html>
