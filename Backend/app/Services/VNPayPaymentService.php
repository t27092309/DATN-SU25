<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VnPayPaymentService
{
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $vnp_Url;
    protected $vnp_Returnurl;
    protected $vnp_IpAdd; // This will come from the request

    public function __construct()
    {
        $this->vnp_TmnCode = config('services.vnpay.tmn_code');
        $this->vnp_HashSecret = config('services.vnpay.hash_secret');
        $this->vnp_Url = config('services.vnpay.url');
        $this->vnp_Returnurl = config('services.vnpay.return_url');
    }

    /**
     * Creates a payment request to VNPAY.
     *
     * @param float $amount The total amount to be paid.
     * @param int $orderId The ID of your internal order.
     * @param int $paymentId The ID of your internal payment record.
     * @param string $ipAddress Client IP address.
     * @param string $orderInfo Description for the VNPAY payment.
     * @return array Contains 'status', 'message', 'payUrl', 'rawResponse'
     */
    public function createPayment(float $amount, int $orderId, int $paymentId, string $ipAddress, string $orderInfo = 'Thanh toan don hang'): array
    {
        Log::info("VnPayPaymentService: Initiating payment for Order ID: {$orderId}, Payment ID: {$paymentId}, Amount: {$amount}");

        $vnp_TxnRef = (string)$orderId . '_' . $paymentId . '_' . time(); // Unique transaction reference
        $vnp_Amount = $amount * 100; // VNPAY expects amount in cents/dong units
        $vnp_OrderType = 'billpayment';
        $vnp_Locale = 'vn';
        $vnp_CurrCode = 'VND';
        $vnp_CreateDate = Carbon::now('Asia/Ho_Chi_Minh')->format('YmdHis');
        $vnp_ExpireDate = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(15)->format('YmdHis'); // 15 minutes expiry

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => $vnp_CurrCode,
            "vnp_IpAddr" => $ipAddress,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $orderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
            // "vnp_BankCode" => "" // Optional: specific bank code
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        Log::info("VnPayPaymentService: Generated VNPAY URL.", ['url' => $vnp_Url]);

        return [
            'status' => 'success',
            'message' => 'Yêu cầu thanh toán VNPAY thành công.',
            'payUrl' => $vnp_Url,
            'rawResponse' => $inputData // Store input data as raw response for logging/debugging
        ];
    }

    // You might also need methods for verifying VNPAY IPN in your PaymentController
}