<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $brands = Brand::orderBy('id', 'desc')->paginate(15);

    return response()->json($brands, 200);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|unique:brands,slug',
        'logo' => 'nullable|string',
    ];

    $messages = [
        'name.required' => 'Tên thương hiệu là bắt buộc.',
        'name.string' => 'Tên thương hiệu phải là chuỗi ký tự.',
        'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự.',
        'slug.required' => 'Slug là bắt buộc.',
        'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
    ];

    $validated = $request->validate($rules, $messages);

    $brand = Brand::create($validated);

    return response()->json($brand, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        return response()->json($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

    $rules = [
        'name' => 'sometimes|required|string|max:255',
        'slug' => "sometimes|required|string|unique:brands,slug,$id",
        'logo' => 'nullable|string',
    ];

    $messages = [
        'name.required' => 'Tên thương hiệu là bắt buộc.',
        'name.string' => 'Tên thương hiệu phải là chuỗi ký tự.',
        'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự.',
        'slug.required' => 'Slug là bắt buộc.',
        'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác.',
    ];

    $validated = $request->validate($rules, $messages);

    $brand->update($validated);

    return response()->json($brand);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(['message' => 'Xoá Brand thành công']);
    }
}
