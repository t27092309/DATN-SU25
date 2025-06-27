<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Attribute; // Dùng để eager load trong indexByAttribute
use App\Http\Resources\AttributeValueResource; // <-- ĐẢM BẢO IMPORT
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributeValues = AttributeValue::with('attribute')->latest()->paginate(10);
        // TRẢ VỀ RESOURCE COLLECTION CHO DANH SÁCH
        return AttributeValueResource::collection($attributeValues);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'attribute_id' => [
                'required',
                'integer',
                'exists:attributes,id',
            ],
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                }),
            ],
        ]);

        $attributeValue = AttributeValue::create($validatedData);

        // Eager load attribute để resource có thể bao gồm nó (nếu bạn muốn)
        $attributeValue->load('attribute');

        // TRẢ VỀ RESOURCE ĐƠN LẺ CHO ITEM MỚI TẠO
        return new AttributeValueResource($attributeValue); // Laravel tự động trả về 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeValue $attributeValue)
    {
        // Eager load thông tin thuộc tính cha
        $attributeValue->load('attribute');
        // TRẢ VỀ RESOURCE ĐƠN LẺ CHO HIỂN THỊ
        return new AttributeValueResource($attributeValue);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttributeValue $attributeValue)
    {
        $validatedData = $request->validate([
            'attribute_id' => [
                'required',
                'integer',
                'exists:attributes,id',
            ],
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                })->ignore($attributeValue->id),
            ],
        ]);

        $attributeValue->update($validatedData);

        // Eager load attribute để resource có thể bao gồm nó (nếu bạn muốn)
        $attributeValue->load('attribute');

        // TRẢ VỀ RESOURCE ĐƠN LẺ CHO ITEM ĐÃ CẬP NHẬT
        return new AttributeValueResource($attributeValue);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();

        // TRẢ VỀ 204 NO CONTENT CHO XÓA THÀNH CÔNG
        return response()->noContent(); // Mã 204 không trả về body
    }

    // This method is correctly using the collection resource already
    public function indexByAttribute(Attribute $attribute)
    {
        $attributeValues = $attribute->attributeValues;
        return AttributeValueResource::collection($attributeValues);
    }
}