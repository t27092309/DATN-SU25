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
        $brands = Brand::orderBy('id', 'desc')->get();

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
            $logoPath = $request->file('logo')->store('public/brands/logos');
            $brand->logo = asset(str_replace('public/', 'storage/', $logoPath)); // Lưu đường dẫn public
        }

        $brand->save();

        return response()->json(['message' => 'Thương hiệu đã được thêm mới thành công!', 'brand' => $brand], 201);
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
                Storage::delete($brand->logo);
            }
            $logoPath = $request->file('logo')->store('public/brands/logos');
            $brand->logo = $logoPath;
        } elseif ($request->input('clear_logo')) { // Xử lý nếu người dùng muốn xóa logo hiện tại
            if ($brand->logo) {
                Storage::delete($brand->logo);
                $brand->logo = null;
            }
        }

        $brand->save();

        return response()->json(['message' => 'Thương hiệu đã được cập nhật thành công!', 'brand' => $brand], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $brand = Brand::findOrFail($id); // Tìm Brand theo ID

            // LOGGING: Ghi log trước khi xóa
            Log::info('Attempting to soft delete brand:', ['brand_id' => $id, 'brand_name' => $brand->name]);

            $brand->delete(); // <-- Đảm bảo bạn gọi PHƯƠNG THỨC delete() này cho soft delete

            // LOGGING: Ghi log sau khi xóa thành công
            Log::info('Brand soft deleted successfully:', ['brand_id' => $id]);

            return response()->json(['message' => 'Thương hiệu đã được chuyển vào thùng rác thành công!'], 200);
        } catch (\Exception $e) {
            // LOGGING: Ghi log nếu có lỗi khi xóa
            Log::error('Error soft deleting brand:', ['brand_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Lỗi khi xóa mềm thương hiệu: ' . $e->getMessage()], 500);
        }
    }

    public function trashed()
    {
        dd('trashed method reached!'); // <-- Thêm dòng này để debug

        // Thêm log để kiểm tra trước khi truy vấn
        Log::info('Attempting to fetch trashed brands...');

        try {
            $brands = Brand::onlyTrashed()
                ->orderBy('id', 'desc')
                ->get();

            Log::info('Fetched trashed brands count:', ['count' => $brands->count()]);
            Log::info('First trashed brand data (if any):', $brands->first() ? $brands->first()->toArray() : 'None');

            // ... (Logic transform logo nếu có) ...

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

        // Xóa logo vật lý nếu tồn tại trước khi xóa vĩnh viễn
        if ($brand->logo) {
            $logoPath = str_replace(asset('storage/'), 'public/', $brand->logo);
            if (Storage::exists($logoPath)) {
                Storage::delete($logoPath);
            }
        }

        $brand->forceDelete();

        return response()->json(['message' => 'Xóa vĩnh viễn thương hiệu thành công!'], 200);
    }
}
