<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Lấy danh sách danh mục
    // GET // http://localhost:8000/api/admin/categories
    public function index()
    {
        $categories = Category::orderBy('id', 'desc')->paginate(15);
        return response()->json($categories, 200);
    }

    // Lấy danh sách danh mục đã bị xóa mềm
    // GET // http://localhost:8000/api/admin/categories/trashed
    public function trashed()
    {
        $categories = Category::onlyTrashed()->orderBy('id', 'desc')->paginate(15);
        return response()->json($categories, 200);
    }

     // Tạo danh mục mới
    // POST // http://localhost:8000/api/admin/categories
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

    // Chi tiết danh mục
    // GET //  http://localhost:8000/api/admin/categories/{id}
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category, 200);
    }

    // Cập nhật danh mục
    // PUT // http://localhost:8000/api/admin/categories/{id}
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

    // Xóa mềm danh mục
    // DELETE // http://localhost:8000/api/admin/categories/{id}
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Xóa mềm danh mục thành công '], 200);
    }

    // Khôi phục danh mục đã xóa mềm

    // PUT // http://localhost:8000/api/admin/categories/{id}/restore
        public function restore(string $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return response()->json(['message' => 'Khôi phục danh mục thành công'], 200);
    }

     // Xóa vĩnh viễn danh mục
    // DELETE // http://localhost:8000/api/admin/categories/{id}/force
    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        return response()->json(['message' => 'Xóa vĩnh viễn danh mục thành công.']);
    }

}
