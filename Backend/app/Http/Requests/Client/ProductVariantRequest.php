<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule

class ProductVariantRequest extends FormRequest
{
    public function authorize()
    {
        // Điều chỉnh quyền truy cập tại đây (ví dụ: chỉ admin)
        return true; // Tạm thời là true để dễ test
    }

    public function rules()
    {
        // Khi cập nhật một biến thể cụ thể (PUT/PATCH /api/admin/product-variants/{id})
        // 'id' trong URL sẽ là ID của product_variant
        $variantId = $this->route('product_variant'); // Lấy ID biến thể từ route

        return [
            'product_id' => [
                'required',
                'exists:products,id',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],
            // Các thuộc tính khác của biến thể (ví dụ: color, size)
            // Cần là một mảng ID của attribute_values
            'attribute_value_ids' => [
                'nullable', // Có thể có biến thể không có thuộc tính
                'array',
                Rule::exists('attribute_values', 'id')->where(function ($query) {
                    // Tùy chọn: Đảm bảo các attribute_value_ids này thuộc về các attribute của product đó
                    // Sẽ phức tạp hơn nếu bạn muốn validation chặt chẽ ở đây
                }),
            ],
            // Thêm các trường khác của biến thể nếu có (ví dụ: image, sku)
            'image' => 'nullable|string', // Hoặc 'image|mimes:jpeg,png,jpg,gif|max:2048' nếu là upload file
            'sku' => [
                'nullable',
                'string',
                'max:255',
                // Đảm bảo SKU là duy nhất, ngoại trừ chính SKU của biến thể đang cập nhật
                Rule::unique('product_variants')->ignore($variantId),
            ],
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Mã sản phẩm là bắt buộc.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'price.required' => 'Giá biến thể là bắt buộc.',
            'price.numeric' => 'Giá biến thể phải là số.',
            'price.min' => 'Giá biến thể không được âm.',
            'stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'stock.min' => 'Số lượng tồn kho không được âm.',
            'attribute_value_ids.array' => 'Thuộc tính biến thể phải là một mảng.',
            'attribute_value_ids.exists' => 'Một hoặc nhiều giá trị thuộc tính không tồn tại.',
            'sku.unique' => 'Mã SKU này đã tồn tại cho một biến thể khác.',
        ];
    }
}