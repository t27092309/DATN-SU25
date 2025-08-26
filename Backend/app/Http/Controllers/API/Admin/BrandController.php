<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->get()->map(function ($brand) {
            if ($brand->logo) {
                $brand->logo_url = asset('storage/' . $brand->logo);
            }
            return $brand;
        });

        return response()->json($brands, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.mimes' => 'Logo phải có định dạng: jpeg, png, jpg, gif, webp.',
            'logo.max' => 'Kích thước logo không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = $request->input('slug') ?: Str::slug($request->input('name'));

        // Kiểm tra slug có duy nhất không, nếu không thì thêm số
        $originalSlug = $slug;
        $count = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $brand = new Brand();
        $brand->name = $request->input('name');
        $brand->slug = $slug;
        $brand->description = $request->input('description');

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands/logos', 'public');
            $brand->logo = $logoPath;
        }

        $brand->save();

        // Thêm URL cho logo nếu có
        if ($brand->logo) {
            $brand->logo_url = asset('storage/' . $brand->logo);
        }

        return response()->json(['message' => 'Thương hiệu đã được thêm mới thành công!', 'brand' => $brand], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        if ($brand->logo) {
            $brand->logo_url = asset('storage/' . $brand->logo);
        }
        return response()->json($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.mimes' => 'Logo phải có định dạng: jpeg, png, jpg, gif, webp.',
            'logo.max' => 'Kích thước logo không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $brand->name = $request->input('name');
        $brand->description = $request->input('description');

        // Handle slug update
        $newSlug = $request->input('slug') ?: Str::slug($request->input('name'));
        if ($newSlug !== $brand->slug) {
            $originalSlug = $newSlug;
            $count = 1;
            while (Brand::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                $newSlug = $originalSlug . '-' . $count++;
            }
            $brand->slug = $newSlug;
        }

        if ($request->hasFile('logo')) {
            // Xóa logo cũ nếu có
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $logoPath = $request->file('logo')->store('brands/logos', 'public');
            $brand->logo = $logoPath;
        } elseif ($request->input('clear_logo')) { // Xử lý nếu người dùng muốn xóa logo hiện tại
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
                $brand->logo = null;
            }
        }

        $brand->save();

        // Thêm URL cho logo nếu có
        if ($brand->logo) {
            $brand->logo_url = asset('storage/' . $brand->logo);
        }

        return response()->json(['message' => 'Thương hiệu đã được cập nhật thành công!', 'brand' => $brand], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $brand = Brand::findOrFail($id);
            // Kiểm tra xem có sản phẩm nào thuộc thương hiệu này không
            if ($brand->products()->count() > 0) {
                // Trả về lỗi nếu có sản phẩm đang sử dụng thương hiệu này
                return response()->json([
                    'message' => 'Không thể xóa thương hiệu này vì có sản phẩm đang thuộc về nó. '
                ], 409); // Mã 409 Conflict là phù hợp cho trường hợp này
            }

            $brand->delete();

            Log::info('Brand soft deleted successfully:', ['brand_id' => $id]);

            return response()->json(['message' => 'Thương hiệu đã được chuyển vào thùng rác thành công!'], 200);

        } catch (\Exception $e) {
            Log::error('Error soft deleting brand:', ['brand_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Lỗi khi xóa mềm thương hiệu: ' . $e->getMessage()], 500);
        }
    }

    public function trashed()
    {
        // dd('trashed method reached!'); // <-- Thêm dòng này để debug

        // Thêm log để kiểm tra trước khi truy vấn
        Log::info('Attempting to fetch trashed brands...');

        try {
            $brands = Brand::onlyTrashed()
                ->orderBy('id', 'desc')
                ->get();

            Log::info('Fetched trashed brands count:', ['count' => $brands->count()]);

            // CÁCH KHẮC PHỤC CHÍNH: Đảm bảo tham số thứ hai luôn là một mảng
            if ($brands->isNotEmpty()) {
                Log::info('First trashed brand data:', ['data' => $brands->first()->toArray()]);
            } else {
                Log::info('First trashed brand data:', ['data' => 'None']);
            }

            // Hoặc một cách viết ngắn gọn hơn:
            // Log::info('First trashed brand data (if any):', ['data' => $brands->first() ? $brands->first()->toArray() : 'None']);

            // Thêm URL cho logo nếu có
            $brands = $brands->map(function ($brand) {
                if ($brand->logo) {
                    $brand->logo_url = asset('storage/' . $brand->logo);
                }
                return $brand;
            });

            return response()->json($brands, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching trashed brands:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Lỗi server khi lấy dữ liệu thùng rác.'], 500);
        }
    }


    public function restore(string $id)
    {
        $brand = Brand::onlyTrashed()->findOrFail($id);
        $brand->restore();

        return response()->json(['message' => 'Khôi phục thương hiệu thành công!'], 200);
    }

    public function forceDelete(string $id)
    {
        $brand = Brand::onlyTrashed()->findOrFail($id);
        if ($brand->products()->count() > 0) {
            // Trả về lỗi nếu có sản phẩm đang sử dụng thương hiệu này
            return response()->json([
                'message' => 'Không thể xóa vĩnh viễn thương hiệu này vì có sản phẩm đang thuộc về nó.'
            ], 409); // Mã 409 Conflict là phù hợp cho trường hợp này
        }

        // Xóa logo vật lý nếu tồn tại trước khi xóa vĩnh viễn
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->forceDelete();

        return response()->json(['message' => 'Xóa vĩnh viễn thương hiệu thành công!'], 200);
    }

    /**
     * Upload image for brand
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ]);

        try {
            $path = $request->file('image')->store('brands/logos', 'public');

            return response()->json([
                'message' => 'Ảnh đã được tải lên thành công.',
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi tải lên ảnh: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi tải lên ảnh.', 'error' => $e->getMessage()], 500);
        }
    }
}
