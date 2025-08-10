<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

        // Kiểm tra đơn hàng có tồn tại không
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Tạo một bản ghi Payment mới với trạng thái pending
        $payment = Payment::create([
            'order_id' => $orderId,
            'payment_method' => $method,
            'amount' => $amount,
            'payment_status' => 'pending', // Mặc định là pending
            // Các trường khác sẽ được cập nhật sau khi có phản hồi từ cổng thanh toán
        ]);

        // Truyền payment ID tới các hàm xử lý cổng thanh toán
        // Điều này giúp bạn có thể cập nhật bản ghi payment cụ thể sau này
        if ($method === "momo") {
            return $this->handleMomoPayment($amount, $orderId, $payment->id);
        }

        if ($method === "vnpay") {
            return $this->handleVnpayPayment($amount, $orderId, $payment->id);
        }

        // Nếu phương thức không hợp lệ, xóa bản ghi payment vừa tạo
        $payment->delete();
        return response()->json(['message' => 'Phương thức thanh toán không hợp lệ'], 400);
    }


    public function handleMomoPayment($amount, $orderId, $paymentId)
    {
        // 1. Lấy thông tin cấu hình từ .env
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $redirectUrl = env('MOMO_RETURN_URL');
        $ipnUrl = env('MOMO_IPN_URL');

        // 2. Lấy bản ghi Payment đã tạo
        $payment = Payment::find($paymentId);
        if (!$payment) {
            // Trường hợp không tìm thấy bản ghi payment, có thể log lỗi hoặc trả về lỗi
            return response()->json(['message' => 'Lỗi: Không tìm thấy bản ghi thanh toán'], 404);
        }

        // 3. Chuẩn bị dữ liệu yêu cầu MoMo
        // MoMo yêu cầu orderId duy nhất cho mỗi giao dịch.
        // Bạn có thể dùng ID của bản ghi Payment hoặc một UUID.
        // Dùng payment->id sẽ giúp dễ dàng liên kết lại khi callback.
        $momoOrderId = 'ORDER_' . $orderId . '_PMT_' . $payment->id . '_' . Str::random(8);
        $requestId = Str::uuid()->toString(); // requestId cũng cần duy nhất

        $orderInfo = "Thanh toan don hang #" . $orderId . " qua MoMo";
        $extraData = ""; // Dữ liệu bổ sung, có thể để trống hoặc thêm thông tin cần thiết

        // 4. Tạo chuỗi raw hash để ký
        // Đảm bảo thứ tự các tham số đúng như MoMo yêu cầu
        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $ipnUrl .
            "&orderId=" . $momoOrderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $partnerCode .
            "&redirectUrl=" . $redirectUrl .
            "&requestId=" . $requestId .
            "&requestType=captureWallet";

        // 5. Ký chữ ký SHA256 HMAC
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        // 6. Dữ liệu gửi đi dạng JSON
        $data = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => (int)$amount, // Đảm bảo là số nguyên
            'orderId' => $momoOrderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => 'captureWallet', // Loại yêu cầu thanh toán qua ví MoMo
            'signature' => $signature,
            'lang' => 'vi',
        ];

        // 7. Gửi yêu cầu CURL đến MoMo API
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); // Đặt phương thức là POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Lấy HTTP Status Code
        curl_close($ch);

        $response = json_decode($result, true);

        // 8. Lưu thông tin request và response MoMo vào payment_details
        $payment->payment_details = array_merge($payment->payment_details ?? [], [
            'momo_request' => $data,
            'momo_response_initial' => $response,
            'momo_http_code' => $httpCode,
            'momo_order_id_request' => $momoOrderId, // ID MoMo gửi đi
            'momo_request_id_request' => $requestId, // Request ID MoMo gửi đi
        ]);
        $payment->save();

        // 9. Xử lý phản hồi từ MoMo
        if ($httpCode === 200 && isset($response['payUrl'])) {
            return response()->json(['payUrl' => $response['payUrl']]);
        }

        // Nếu có lỗi hoặc không nhận được payUrl, cập nhật trạng thái payment là 'failed'
        $payment->payment_status = 'failed';
        $payment->payment_details = array_merge($payment->payment_details ?? [], [
            'error_message' => 'Failed to get payUrl from MoMo',
            'response_code' => $response['errorCode'] ?? 'N/A', // Lấy mã lỗi nếu có
            'response_message' => $response['message'] ?? 'N/A', // Lấy thông báo lỗi nếu có
        ]);
        $payment->save();

        return response()->json([
            'message' => 'Lỗi tạo yêu cầu thanh toán MoMo',
            'response' => $response,
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
        // Đây là nơi xử lý Return URL (người dùng redirect về)
        // Dữ liệu từ MoMo sẽ nằm trong query parameters (GET request)
        // Bạn sẽ nhận được các thông tin như: partnerCode, orderId, requestId, amount, resultCode, message, responseTime, extraData, signature, etc.

        // 1. Lấy dữ liệu từ Request
        $momoResponse = $request->all();

        // Lấy các tham số cần thiết để xác thực chữ ký (tùy thuộc vào phiên bản MoMo API)
        // Ví dụ: partnerCode, accessKey, requestId, amount, orderId, orderInfo, resultCode, message, localMessage, responseTime, errorCode, extraData, signature
        // MoMo thường không yêu cầu xác thực signature cho Return URL, nhưng IPN thì CÓ.
        // Tuy nhiên, để đảm bảo an toàn, bạn vẫn nên xác thực nếu có thể.

        // Tìm payment record dựa trên orderId của MoMo đã lưu khi gửi request
        $momoOrderId = $request->input('orderId'); // Đây là orderId mà bạn đã gửi lên MoMo API (momoOrderId trong hàm handleMomoPayment)
        $payment = Payment::whereJsonContains('payment_details->momo_order_id_request', $momoOrderId)->first();

        if (!$payment) {
            // Không tìm thấy bản ghi thanh toán, có thể là lỗi hoặc giả mạo. Log lại!
            \Log::warning('MoMo Return URL: Payment record not found for orderId: ' . $momoOrderId, $momoResponse);
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        // Lưu toàn bộ dữ liệu callback vào payment_details
        $payment->payment_details = array_merge($payment->payment_details ?? [], ['momo_return_url_response' => $momoResponse]);
        $payment->save();

        // Thông báo cho người dùng
        if ($request->input('resultCode') == 0) {
            // Thanh toán thành công (Lưu ý: Xác thực lại bằng IPN là quan trọng nhất)
            $message = 'Thanh toán MoMo thành công! Đơn hàng của bạn đang được xử lý.';
            // Bạn có thể chuyển hướng người dùng đến trang thành công
            // return redirect()->route('order.success', ['order_id' => $payment->order_id]);
        } else {
            // Thanh toán thất bại hoặc bị hủy
            $message = 'Thanh toán MoMo thất bại hoặc bị hủy. Mã lỗi: ' . $request->input('message') . ' (' . $request->input('resultCode') . ')';
            // Chuyển hướng người dùng đến trang thất bại
            // return redirect()->route('order.failed', ['order_id' => $payment->order_id]);
        }

        return response()->json([
            'message' => $message,
            'status' => $request->input('resultCode') == 0 ? 'success' : 'failed',
            'payment_info' => $momoResponse
        ]);
    }

    public function handleVnpayCallback(Request $request)
    {
        return response()->json([
            'message' => 'VNPAY callback received',
            'data' => $request->all()
        ]);
    }

public function handleMomoIpn(Request $request)
    {
        Log::info('MoMo IPN: --- BẮT ĐẦU XỬ LÝ IPN REQUEST ---');
        Log::info('MoMo IPN: Raw Request Data.', ['raw_data' => $request->all()]);
        Log::info('MoMo IPN: JSON Request Body.', ['json_data' => $request->json()->all()]);


        // 1. Lấy dữ liệu từ Request Body
        $momoIpnData = $request->json()->all();

        // Kiểm tra xem dữ liệu có rỗng không
        if (empty($momoIpnData)) {
            Log::error('MoMo IPN: Request body is empty or not valid JSON.');
            return response()->json(['message' => 'Invalid or empty request body'], 400);
        }

        // 2. Xác thực chữ ký (RẤT QUAN TRỌNG ĐỂ ĐẢM BẢO TÍNH TOÀN VẸN DỮ LIỆU)
        $partnerCode = $momoIpnData['partnerCode'] ?? null;
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        // Lấy tất cả các trường cần thiết để tạo chữ ký, đảm bảo chúng tồn tại
        $requiredFields = [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId',
            'orderInfo', 'message', 'localMessage', 'responseTime',
            'errorCode', 'transId', 'extraData'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($momoIpnData[$field])) {
                Log::error("MoMo IPN: Missing required field for signature calculation: {$field}. Data: ", $momoIpnData);
                return response()->json(['message' => "Missing required IPN field: {$field}"], 400);
            }
        }

        $amount = $momoIpnData['amount'];
        $orderId = $momoIpnData['orderId'];
        $orderInfo = $momoIpnData['orderInfo'];
        $errorCode = $momoIpnData['errorCode'];
        $message = $momoIpnData['message'];
        $localMessage = $momoIpnData['localMessage'];
        $responseTime = $momoIpnData['responseTime'];
        $requestId = $momoIpnData['requestId'];
        $transId = $momoIpnData['transId'];
        $extraData = $momoIpnData['extraData'];
        $signature = $momoIpnData['signature'] ?? null; // Chữ ký từ MoMo

        // Sắp xếp các tham số theo bảng chữ cái và tạo chuỗi rawHash cho IPN
        // ĐẢM BẢO CHUỖI NÀY KHỚP CHÍNH XÁC VỚI TÀI LIỆU MO-MO IPN SIGNATURE
        // Đây là ví dụ phổ biến, nhưng có thể cần điều chỉnh!
        $rawHashArray = [
            'accessKey' => $accessKey,
            'amount' => $amount,
            'errorCode' => $errorCode,
            'extraData' => $extraData,
            'localMessage' => $localMessage,
            'message' => $message,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'partnerCode' => $partnerCode,
            'requestId' => $requestId,
            'responseTime' => $responseTime,
            'transId' => $transId,
        ];
        ksort($rawHashArray); // Sắp xếp theo key

        $rawHashIpn = http_build_query($rawHashArray, '', '&');

        $calculatedSignature = hash_hmac("sha256", $rawHashIpn, $secretKey);

        Log::info('MoMo IPN: Signature Check Details.', [
            'momo_ipn_signature' => $signature,
            'calculated_rawHash' => $rawHashIpn,
            'calculated_signature' => $calculatedSignature,
            'secret_key_used' => $secretKey, // Cẩn thận với việc log secret key trong production
            'signature_match' => ($calculatedSignature === $signature)
        ]);

        if ($calculatedSignature !== $signature) {
            Log::error('MoMo IPN: Invalid signature. Data: ', $momoIpnData);
            return response()->json(['message' => 'Invalid signature'], 400);
        }
        Log::info('MoMo IPN: Signature verified successfully.');

        // 3. Tìm bản ghi thanh toán dựa trên MoMo Order ID hoặc TransId
        // Bạn đã lưu `orderIdMoMo` trong `payment_details->momo_order_id_request`
        // Kiểm tra cấu trúc JSON bạn lưu, có thể là `->first()` thay vì `->firstWhere()`
        // Hoặc dùng: where('payment_details->momo_order_id_request', $orderId)
        $payment = Payment::where('transaction_id', $transId) // MoMo transId là duy nhất và đáng tin cậy hơn
                            ->orWhereJsonContains('payment_details->momo_order_id_request', $orderId)
                            ->first();

        if (!$payment) {
            Log::warning('MoMo IPN: Payment record not found for MoMo orderId/transId. orderId: ' . $orderId . ', transId: ' . $transId, $momoIpnData);
            return response()->json(['message' => 'Payment record not found'], 404);
        }
        Log::info('MoMo IPN: Found Payment record.', ['payment_id' => $payment->id, 'current_status' => $payment->payment_status]);


        // 4. Cập nhật trạng thái thanh toán và đơn hàng
        // Chỉ cập nhật nếu trạng thái hiện tại là 'pending' để tránh ghi đè
        if ($payment->payment_status === 'pending') {
            // Đảm bảo payment_details là một mảng để tránh lỗi khi merge
            $currentPaymentDetails = is_array($payment->payment_details) ? $payment->payment_details : [];
            $payment->payment_details = array_merge($currentPaymentDetails, ['momo_ipn_response' => $momoIpnData]);

            if ($errorCode == 0) { // MoMo errorCode 0 là thành công
                $payment->payment_status = 'paid';
                $payment->transaction_id = $transId; // ID giao dịch của MoMo
                $payment->paid_at = now(); // Thời gian thanh toán thành công
                Log::info('MoMo IPN: Giao dịch MoMo thành công (errorCode 0). Cập nhật trạng thái Payment thành "paid".');

                // Cập nhật trạng thái đơn hàng chính
                $order = $payment->order; // Lấy Order Model thông qua quan hệ
                if ($order) {
                    $order->status = 'paid'; // Hoặc trạng thái phù hợp (ex: processing)
                    $order->save();
                    Log::info('MoMo IPN: Cập nhật trạng thái Order thành "paid".', ['order_id' => $order->id]);
                } else {
                    Log::error('MoMo IPN: Order not found for payment ID: ' . $payment->id);
                }
            } else {
                $payment->payment_status = 'failed';
                $payment->payment_details = array_merge($currentPaymentDetails, [
                    'momo_error_code' => $errorCode,
                    'momo_error_message' => $message
                ]);
                Log::warning('MoMo IPN: Giao dịch MoMo thất bại (errorCode ' . $errorCode . '). Cập nhật trạng thái Payment thành "failed".');
            }
            $payment->save();
            Log::info('MoMo IPN: Payment record saved successfully.', ['payment_id' => $payment->id, 'new_status' => $payment->payment_status]);

        } else {
            Log::info('MoMo IPN: Payment ID ' . $payment->id . ' already processed or not pending. Current status: ' . $payment->payment_status);
        }

        // 5. Trả về phản hồi cho MoMo
        Log::info('MoMo IPN: Hoàn tất xử lý IPN. Trả về HTTP 200 OK.');
        return response()->json(['message' => 'IPN received and processed successfully'], 200);
    }
}
