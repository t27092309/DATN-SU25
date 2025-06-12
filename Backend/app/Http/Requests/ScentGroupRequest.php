<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScentGroupRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:scent_groups,name,' . $this->route('scent_group'),
             'color_code' => 'required|string|regex:/^#([a-fA-F0-9]{6})/',
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
            'color_code.regex' => 'Mã màu phải có dấu # và 6 ký tự hex đằng sau (0-9, a-f, A-F), ví dụ: #Fa1234',
        ];
    }
}
