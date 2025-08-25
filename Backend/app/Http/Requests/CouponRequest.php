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
            'discount_value' => 'required|numeric|min:1',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
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
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'discount_value.max' => 'Giá trị giảm phải nhỏ hơn hoặc bằng 100% khi loại giảm giá là phần trăm.',

            'expires_at.date' => 'Ngày hết hạn không hợp lệ.',
            'expires_at.after_or_equal' => 'Ngày hết hạn không được ở trong quá khứ.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',

            'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',
            'usage_limit.min' => 'Giới hạn sử dụng phải lớn hơn hoặc bằng 1.',

            'per_user_limit.integer' => 'Giới hạn mỗi người dùng phải là số nguyên.',
            'per_user_limit.min' => 'Giới hạn mỗi người dùng phải lớn hơn hoặc bằng 1.',

            'min_order_amount.numeric' => 'Giá trị đơn hàng tối thiểu phải là số.',
            'min_order_amount.min' => 'Giá trị đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.',

            'max_discount.numeric' => 'Giảm tối đa phải là số.',
            'max_discount.min' => 'Giảm tối đa phải lớn hơn 0.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('discount_type');
            $value = $this->input('discount_value');

            if ($type === 'percent') {
                if ($value > 100) {
                    $validator->errors()->add(
                        'discount_value',
                        'Giá trị giảm phải nhỏ hơn hoặc bằng 100% khi loại giảm giá là phần trăm.'
                    );
                }
            }

            // 2. Validation cho `max_discount` (áp dụng cho cả 2 loại)
            if ($type === 'percent' && !$this->filled('max_discount')) {
                // Bắt buộc nhập giá trị giảm tối đa khi là phần trăm
                $validator->errors()->add(
                    'max_discount',
                    'Khi chọn loại giảm giá theo %, bạn phải nhập giảm tối đa và phải lớn hơn 0.'

                );
            }

            if ($type === 'fixed' && $this->filled('max_discount')) {
                // Không được nhập giá trị giảm tối đa khi là cố định
                $validator->errors()->add(
                    'max_discount',
                    'Không được nhập giảm tối đa khi chọn loại giảm giá cố định.'
                );
            }

            // --- Kết thúc validation tùy chỉnh ---
        });
    }
}
