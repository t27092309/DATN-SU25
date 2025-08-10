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
        if ($this->isMethod('post')) {
            return [
                'quantity' => 'required|integer|min:1',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'product_id' => 'required_without:product_variant_id|exists:products,id',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'quantity' => 'sometimes|integer|min:1',
                'product_variant_id' => 'nullable|exists:product_variants,id',
            ];
        }

        return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('product_id') && $this->filled('product_variant_id')) {
                $validator->errors()->add('product_id', 'Không được gửi cả product_id và product_variant_id cùng lúc.');
            }
        });
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.required_without' => 'Vui lòng chọn sản phẩm khi không có biến thể được chọn.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'product_id.integer' => 'ID sản phẩm phải là số nguyên.',

            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'product_variant_id.integer' => 'ID biến thể phải là số nguyên.',

            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
        ];
    }
}
