<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Lấy danh sách tất cả banner, có phân trang.
     */
    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->paginate(10);
        return response()->json($banners);
    }

    /**
     * Thêm mới một banner vào storage.
     */
    public function store(BannerRequest $request)
    {
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        $banner = Banner::create([
            'title' => $request->title,
            'image_url' => $imageUrl,
            'link_url' => $request->link_url,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Banner created successfully!',
            'banner' => $banner
        ], 201);
    }

    /**
     * Lấy chi tiết một banner.
     */
    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json($banner);
    }

    /**
     * Cập nhật một banner đã tồn tại.
     */
    public function update(BannerRequest $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        // Use input() to handle nullable fields and retain old values
        $banner->title = $request->input('title', $banner->title);
        $banner->link_url = $request->input('link_url', $banner->link_url);
        $banner->description = $request->input('description', $banner->description);
        $banner->is_active = $request->input('is_active', $banner->is_active);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($banner->image_url) {
                $oldImagePath = Str::after($banner->image_url, '/storage/');
                Storage::disk('public')->delete($oldImagePath);
            }

            // Upload new image
            $imagePath = $request->file('image')->store('banners', 'public');
            $banner->image_url = Storage::url($imagePath);
        }

        $banner->save();

        return response()->json([
            'message' => 'Banner updated successfully!',
            'banner' => $banner
        ]);
    }

    /**
     * Xóa một banner khỏi storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image_url) {
            $imagePath = Str::after($banner->image_url, '/storage/');
            Storage::disk('public')->delete($imagePath);
        }

        $banner->delete();

        return response()->json([
            'message' => 'Banner deleted successfully!'
        ], 200);
    }
}
