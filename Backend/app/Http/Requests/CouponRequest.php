<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . $this->route('coupon'),
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',

        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã giảm giá không được để trống',
            'code.string' => 'Mã giảm giá phải là một chuỗi.',
            'code.max' => 'Mã giảm giá không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'discount_type.required' => 'Loại giảm giá không được để trống',
            'discount_type.in' => 'Loại giảm giá phải là percent hoặc fixed.',
            'discount_value.required' => 'Giá trị giảm giá là không được để trống',
            'discount_value.numeric' => 'Giá trị giảm giá phải là một số.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',
            'expires_at.date' => 'Ngày hết hạn không hợp lệ.',
        ];
    }
}
