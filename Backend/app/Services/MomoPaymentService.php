<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MomoPaymentService
{
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $returnUrl;
    protected $notifyUrl;
    protected $endpoint;

    public function __construct()
    {
        $this->partnerCode = config('services.momo.partner_code');
        $this->accessKey = config('services.momo.access_key');
        $this->secretKey = config('services.momo.secret_key');
        $this->returnUrl = config('services.momo.return_url');
        $this->notifyUrl = config('services.momo.notify_url');
        $this->endpoint = config('services.momo.endpoint');
    }

    public function createPayment(float $amount, int $orderId, int $paymentId, string $orderInfo = 'Thanh toán đơn hàng'): array
    {
        Log::info("MomoPaymentService: Initiating payment for Order ID: {$orderId}, Payment ID: {$paymentId}, Amount: {$amount}");

        $requestId = 'ORDER_' . $orderId . '_PAYMENT_' . $paymentId . '_' . Str::random(10);
        $orderIdMoMo = $requestId; // MoMo's orderId must be unique per transaction

        $amount = (int)$amount;

        $extraData = base64_encode(json_encode([
            'order_id' => $orderId,
            'payment_id' => $paymentId,
        ]));

        if (empty($this->notifyUrl)) {
             Log::error("MomoPaymentService: notifyUrl is null or empty. Check .env and config/services.php. Current value: " . $this->notifyUrl);
        }

        // --- CRITICAL FIX: Reconstruct rawHash exactly as MoMo expects ---
        // Parameters must be sorted alphabetically and use their exact names (e.g., ipnUrl, redirectUrl)
        $rawHash = "accessKey={$this->accessKey}" .
                   "&amount={$amount}" .
                   "&extraData={$extraData}" .
                   "&ipnUrl={$this->notifyUrl}" . // Changed from notifyUrl to ipnUrl here
                   "&orderId={$orderIdMoMo}" .
                   "&orderInfo={$orderInfo}" .
                   "&partnerCode={$this->partnerCode}" .
                   "&redirectUrl=" . // Added redirectUrl for hashing
                   "&requestId={$requestId}" .
                   "&requestType=captureWallet"; // Hardcoded requestType for hashing

        $signature = hash_hmac('sha256', $rawHash, $this->secretKey);

        $payload = [
            'partnerCode' => $this->partnerCode,
            'accessKey' => $this->accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderIdMoMo,
            'orderInfo' => $orderInfo,
            'returnUrl' => $this->returnUrl, // Keep this name for the payload
            'ipnUrl' => $this->notifyUrl,    // Keep this name for the payload
            'extraData' => $extraData,
            'requestType' => 'captureWallet', // Keep this name for the payload
            'signature' => $signature,
            // 'lang' => 'vi', // Removed this from payload temporarily for signature consistency
        ];

        Log::info("MomoPaymentService: Sending request to MoMo API.", ['payload' => $payload]);

        try {
            $response = Http::timeout(30)->post($this->endpoint, $payload);
            $responseData = $response->json();

            Log::info("MomoPaymentService: Received response from MoMo API.", ['response' => $responseData]);

            if ($response->successful() && isset($responseData['resultCode']) && $responseData['resultCode'] == 0) {
                return [
                    'status' => 'success',
                    'message' => 'Yêu cầu thanh toán MoMo thành công.',
                    'payUrl' => $responseData['payUrl'] ?? null,
                    'qrCodeUrl' => $responseData['qrCodeUrl'] ?? null,
                    'transId' => $responseData['transId'] ?? null,
                    'rawResponse' => $responseData
                ];
            } else {
                return [
                    'status' => 'failed',
                    'message' => $responseData['message'] ?? 'Lỗi không xác định từ MoMo hoặc giao dịch thất bại.',
                    'errorCode' => $responseData['resultCode'] ?? (isset($responseData['errorCode']) ? $responseData['errorCode'] : -1),
                    'rawResponse' => $responseData
                ];
            }
        } catch (\Exception $e) {
            Log::error("MomoPaymentService: Exception during MoMo payment request for Order ID: {$orderId}. Error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'error',
                'message' => 'Lỗi kết nối hoặc xử lý yêu cầu MoMo: ' . $e->getMessage(),
                'rawResponse' => null
            ];
        }
    }
}