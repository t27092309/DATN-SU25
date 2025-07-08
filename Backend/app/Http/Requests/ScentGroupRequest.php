<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScentGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $scentGroupId = $this->route('scent_group') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255|unique:scent_groups,name,' . $scentGroupId . ',id',
            'color_code' => 'required|string|regex:/^#([a-fA-F0-9]{6})$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên nhóm hương không được để trống',
            'name.string' => 'Tên nhóm hương phải là một chuỗi',
            'name.max' => 'Tên nhóm hương không được vượt quá 255 ký tự',
            'name.unique' => 'Tên nhóm hương đã tồn tại',

            'color_code.required' => 'Mã màu không được để trống',
            'color_code.string' => 'Mã màu phải là một chuỗi',
            'color_code.regex' => 'Mã màu phải đúng định dạng #RRGGBB (ví dụ: #FA1234)',
        ];
    }
}
