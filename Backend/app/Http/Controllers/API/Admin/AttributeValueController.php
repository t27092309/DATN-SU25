<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Attribute; // Để kiểm tra tồn tại attribute_id
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các giá trị thuộc tính, eager load thông tin thuộc tính cha
        $attributeValues = AttributeValue::with('attribute')->latest()->paginate(10);
        return response()->json($attributeValues);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => [
                'required',
                'integer',
                'exists:attributes,id', // Đảm bảo attribute_id tồn tại trong bảng attributes
            ],
            'value' => [
                'required',
                'string',
                'max:255',
                // Đảm bảo cặp attribute_id và value là duy nhất
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                }),
            ],
        ]);

        $attributeValue = AttributeValue::create([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
        ]);

        // Eager load attribute để trả về đầy đủ thông tin
        $attributeValue->load('attribute');

        return response()->json([
            'message' => 'Attribute Value created successfully.',
            'attribute_value' => $attributeValue
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeValue $attributeValue)
    {
        // Eager load thông tin thuộc tính cha khi hiển thị một giá trị cụ thể
        $attributeValue->load('attribute');
        return response()->json($attributeValue);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttributeValue $attributeValue)
    {
        $request->validate([
            'attribute_id' => [
                'required',
                'integer',
                'exists:attributes,id',
            ],
            'value' => [
                'required',
                'string',
                'max:255',
                // Đảm bảo cặp attribute_id và value là duy nhất, bỏ qua chính nó
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                })->ignore($attributeValue->id),
            ],
        ]);

        $attributeValue->update([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
        ]);

        // Eager load attribute để trả về đầy đủ thông tin
        $attributeValue->load('attribute');

        return response()->json([
            'message' => 'Attribute Value updated successfully.',
            'attribute_value' => $attributeValue
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();

        return response()->json([
            'message' => 'Attribute Value deleted successfully.'
        ], 204);
    }
}
