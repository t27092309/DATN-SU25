<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Nếu là POST (store)
        if ($this->isMethod('post')) {
            return [
                'quantity' => 'required|integer|min:1',
                'product_variant_id' => 'required|exists:product_variants,id',
            ];
        }

        // Nếu là PUT/PATCH (update)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'quantity' => 'sometimes|integer|min:1',
                'product_variant_id' => 'nullable|exists:product_variants,id'
            ];
        }

        return [];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
        ];
    }
}
