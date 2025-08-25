<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        return Post::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/posts', 'public');
        }

        $post = Post::create($validated);
        return response()->json(['message' => 'Tạo bài viết thành công!', 'data' => $post], 201);
    }

    public function show(Post $post)
    {
        return $post;
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('images/posts', 'public');
        }

        $post->update($validated);
        return response()->json(['message' => 'Cập nhật bài viết thành công!', 'data' => $post]);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Xóa mềm bài viết thành công!'], 204);
    }

    public function trashed()
    {
        $posts = Post::onlyTrashed()->get();
        return response()->json(['data' => $posts]);
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();
        return response()->json(['message' => 'Khôi phục bài viết thành công!', 'data' => $post]);
    }

    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->forceDelete();
        return response()->json(['message' => 'Xóa vĩnh viễn bài viết thành công!'], 204);
    }

    public function uploadImage(Request $request, Post $post)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $path = $request->file('image')->store('images/posts', 'public');
            $post->update(['image' => $path]);
            return response()->json(['message' => 'Upload ảnh thành công!', 'image' => $path], 200);
        }

        return response()->json(['message' => 'Không có ảnh được tải lên!'], 400);
    }
}