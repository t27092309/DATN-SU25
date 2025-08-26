<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Có thể thêm logic phân quyền nếu cần
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['momo', 'vnpay'])],
            'amount' => ['required', 'numeric', 'min:1000'],
            'order_id' => ['required', 'exists:orders,id'],
        ];
    }

    /**
     * Get custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'method.required' => 'Phương thức thanh toán là bắt buộc.',
            'method.in' => 'Phương thức thanh toán phải là momo hoặc vnpay.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền tối thiểu là 1000.',
            'order_id.required' => 'ID đơn hàng là bắt buộc.',
            'order_id.exists' => 'Đơn hàng không tồn tại.',
        ];
    }
}
