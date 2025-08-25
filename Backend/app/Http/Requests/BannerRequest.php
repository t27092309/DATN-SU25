<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Set up base rules for all requests
        $rules = [
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean' // Changed to nullable
        ];

        // If the request is a POST (create), the image is required
        if ($this->isMethod('POST')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        // If the request is a PUT/PATCH (update), the image is nullable
        // Note: The logic for PUT/PATCH is now a bit more flexible.
        // Even if a field is not present in the request, the controller's
        // `input()` method will handle it, making these rules robust.
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'image.required' => 'Bạn chưa chọn ảnh cho banner.',
            'image.image' => 'File tải lên phải là một hình ảnh.',
            'image.mimes' => 'Hình ảnh chỉ được phép có định dạng: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'link_url.url' => 'Đường dẫn không hợp lệ.',
            'link_url.max' => 'Đường dẫn không được vượt quá 255 ký tự.',
        ];
    }
}
