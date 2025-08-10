<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentMethodController extends Controller
{
        public function index()
    {
        // Đảm bảo bạn đã định nghĩa hằng số PAYMENT_METHODS trong App\Models\Payment
        return response()->json([
            'message' => 'Danh sách phương thức thanh toán',
            'data' => Payment::PAYMENT_METHODS,
        ]);
    }
}
