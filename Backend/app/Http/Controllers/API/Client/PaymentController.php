<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // POST // http://localhost:8000/api/payment/create
    // TEST POSTMAN
    //MOMO
    //         {
    //   "method": "momo",
    //   "amount": 130000,
    //   "order_id": 10
    // }
    public function createPayment(Request $request)
    {
        $method = $request->input('method');
        $amount = $request->input('amount');
        $orderId = $request->input('order_id');

        if ($method === "momo") {
            return $this->handleMomoPayment($amount, $orderId);
        }

        if ($method === "vnpay") {
            return $this->handleVnpayPayment($amount, $orderId);
        }

        return response()->json(['message' => 'Phương thức thanh toán không hợp lệ'], 400);
    }

    public function handleMomoPayment($amount, $orderId)
    {
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        $orderInfo = "Thanh toán đơn hàng #" . $orderId;
        $redirectUrl = env('MOMO_RETURN_URL');
        $ipnUrl = $redirectUrl;
        $orderIdGen = uniqid();
        $requestId = uniqid();

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=&ipnUrl=$ipnUrl&orderId=$orderIdGen&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderIdGen,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => '',
            'requestType' => 'captureWallet',
            'signature' => $signature,
            'lang' => 'vi',
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if (isset($result['payUrl'])) {
            return response()->json(['payUrl' => $result['payUrl']]);
        }

        return response()->json([
            'message' => 'Lỗi tạo yêu cầu thanh toán MoMo',
            'response' => $result,
        ], 400);
    }

    public function handleVnpayPayment($amount, $orderId)
    {
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = env('VNPAY_RETURN_URL');
        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET'); // Đúng tên biến

        $vnp_TxnRef = uniqid();
        $vnp_OrderInfo = "Thanh toán đơn hàng #" . $orderId;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $amount * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();
        $vnp_CreateDate = date('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // Sắp xếp key theo thứ tự a-z
        ksort($inputData);

        // Tạo chuỗi dữ liệu để hash (KHÔNG urlencode)
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($hashdata != '') {
                $hashdata .= '&';
            }
            $hashdata .= $key . '=' . $value;
        }

        // Tạo secure hash
        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        // Tạo URL query (lúc này mới urlencode)
        $inputData["vnp_SecureHash"] = $vnp_SecureHash;
        $query = http_build_query($inputData);
        $paymentUrl = $vnp_Url . '?' . $query;

        return response()->json(['payUrl' => $paymentUrl]);
    }

    public function handleMomoCallback(Request $request)
    {
        return response()->json([
            'message' => 'Momo callback received',
            'data' => $request->all()
        ]);
    }

    public function handleVnpayCallback(Request $request)
    {
        return response()->json([
            'message' => 'VNPAY callback received',
            'data' => $request->all()
        ]);
    }
}
