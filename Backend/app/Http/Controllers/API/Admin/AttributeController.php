<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các thuộc tính, có thể phân trang hoặc eager load các giá trị thuộc tính nếu cần
        $attributes = Attribute::with('values')->latest()->paginate(10);
        return response()->json($attributes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name'],
            // Slug sẽ được tạo tự động, không cần nhập từ người dùng
        ]);

        $attribute = Attribute::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Attribute created successfully.',
            'attribute' => $attribute
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        // Eager load các giá trị thuộc tính khi hiển thị một thuộc tính cụ thể
        $attribute->load('attributeValues');
        return response()->json($attribute);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes')->ignore($attribute->id),
            ],
        ]);

        $attribute->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Cập nhật lại slug nếu tên thay đổi
        ]);

        return response()->json([
            'message' => 'Attribute updated successfully.',
            'attribute' => $attribute
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return response()->json([
            'message' => 'Attribute deleted successfully.'
        ], 204); // 204 No Content
    }
}