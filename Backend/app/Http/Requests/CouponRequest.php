<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . ($this->coupon ?? 'NULL') . ',id',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.string' => 'Mã giảm giá phải là chuỗi ký tự.',
            'code.max' => 'Mã giảm giá không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã giảm giá này đã tồn tại.',

            'discount_type.required' => 'Vui lòng chọn loại giảm giá.',
            'discount_type.in' => 'Loại giảm giá phải là phần trăm hoặc cố định.',

            'discount_value.required' => 'Vui lòng nhập giá trị giảm.',
            'discount_value.numeric' => 'Giá trị giảm phải là số.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn hoặc bằng 0.',

            'expires_at.date' => 'Ngày hết hạn không hợp lệ.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',

            'min_order_amount.numeric' => 'Giá trị đơn hàng tối thiểu phải là số.',
            'min_order_amount.min' => 'Giá trị đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.',

            'max_discount.numeric' => 'Giảm tối đa phải là số.',
            'max_discount.min' => 'Giảm tối đa phải lớn hơn hoặc bằng 0.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('discount_type');

            if ($type === 'percent' && !$this->filled('max_discount')) {
                $validator->errors()->add(
                    'max_discount',
                    'Khi chọn loại giảm giá theo %, bạn phải nhập giảm tối đa.'
                );
            }

            if ($type === 'fixed' && $this->filled('max_discount')) {
                $validator->errors()->add(
                    'max_discount',
                    'Không được nhập giảm tối đa khi chọn loại giảm giá cố định.'
                );
            }
        });
    }
}
