<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CategoryController extends Controller
{

    // GET // http://localhost:8000/api/categories
    public function index()
    {
        return response()->json(Category::all(), 200);
    }

    // POST // http://localhost:8000/api/categories
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $category = Category::create($data);
        return response()->json([
            'message' => 'Thêm danh mục thành công',
            'category' => $category,
        ], 201);
    }

    //GET // http://localhost:8000/api/categories/{id}
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category, 200);
    }

    //PUT // http://localhost:8000/api/categories/{id}
    public function update(CategoryRequest $request, string $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'category' => $category,
        ], 200);
    }

    //DELETE // http://localhost:8000/api/categories/{id}
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Xóa danh mục thành công'], 200);
    }

    
}
