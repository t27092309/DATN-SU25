<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    /**
     * Xác định xem user có được phép thực hiện request này không.
     */
    public function authorize(): bool
    {
        // Chỉ user có role 'admin' mới được phép gửi request này
        return $this->user()->isAdmin();
    }

    /**
     * Lấy các quy tắc validation áp dụng cho request.
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                // Admin chỉ có thể thay đổi sang 'user' hoặc 'staff'
                Rule::in(['user', 'staff']),
            ],
        ];
    }

    /**
     * Lấy các thông báo lỗi tùy chỉnh.
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Trường vai trò là bắt buộc.',
            'role.string' => 'Vai trò phải là một chuỗi.',
            'role.in' => 'Vai trò chỉ có thể là "user" hoặc "staff".',
        ];
    }
}