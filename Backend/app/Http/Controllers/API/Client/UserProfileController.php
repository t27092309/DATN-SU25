<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    // Lấy thông tin hồ sơ cá nhân
    // public function show(Request $request)
    // {
    //     return response()->json($request->user());
    // }

    // // Cập nhật thông tin hồ sơ cá nhân
    // public function update(Request $request)
    // {
    //     $user = $request->user();

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => [
    //             'required',
    //             'email',
    //             'max:255',
    //             Rule::unique('users')->ignore($user->id),
    //         ],
    //         'phone_number' => 'nullable|string|max:20',
    //         'gender' => 'nullable|in:male,female,other',
    //         'birthday' => 'nullable|date',
    //         'password' => 'nullable|string|min:6|confirmed',
    //         'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ], [
    //         'name.required' => 'Tên không được để trống.',
    //         'name.max' => 'Tên không được vượt quá 255 ký tự.',
    //         'name.string' => 'Tên phải là chuỗi ký tự.',

    //         'email.required' => 'Email không được để trống.',
    //         'email.email' => 'Email không đúng định dạng.',
    //         'email.max' => 'Email không được vượt quá 255 ký tự.',
    //         'email.unique' => 'Email đã tồn tại.',

    //         'phone_number.string' => 'Số điện thoại phải là chuỗi.',
    //         'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

    //         'gender.in' => 'Giới tính phải là male, female hoặc other.',

    //         'birthday.date' => 'Ngày sinh phải là định dạng ngày hợp lệ.',

    //         'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
    //         'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

    //         'avatar.image' => 'Ảnh đại diện phải là tệp hình ảnh.',
    //         'avatar.mimes' => 'Ảnh đại diện chỉ được định dạng jpeg, png, jpg.',
    //         'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
    //     ]);

    //     $user->fill($validated);

    //     $user->phone_number = $validated['phone_number'] ?? null;
    //     $user->gender = $validated['gender'] ?? null;
    //     $user->birthday = $validated['birthday'] ?? null;

    //     // Xử lý ảnh đại diện mới (avatar)
    //     if ($request->hasFile('avatar')) {
    //         // Xoá ảnh cũ nếu tồn tại
    //         if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
    //             Storage::disk('public')->delete($user->avatar);
    //         }

    //         // 👉 Lưu ảnh mới
    //         $path = $request->file('avatar')->store('avatars', 'public');
    //         $user->avatar = $path;
    //     }

    //     // Cập nhật mật khẩu nếu có
    //     if (!empty($validated['password'])) {
    //         $user->password = Hash::make($validated['password']);
    //     }

    //     $user->save();

    //     return response()->json([
    //         'message' => 'Cập nhật thông tin thành công.',
    //         'user' => $user,
    //     ]);
    // }

    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'gender' => 'nullable|in:male,female,other',
        'birthday' => 'nullable|date',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
    ], [
        'name.required' => 'Tên là bắt buộc.',
        'name.string' => 'Tên phải là chuỗi ký tự.',
        'name.max' => 'Tên không được vượt quá 255 ký tự.',

        'phone_number.string' => 'Số điện thoại phải là chuỗi ký tự.',
        'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

        'gender.in' => 'Giới tính phải là male, female hoặc other.',

        'birthday.date' => 'Ngày sinh không hợp lệ.',

        'avatar.image' => 'Avatar phải là một ảnh.',
        'avatar.mimes' => 'Avatar phải có định dạng: jpg, jpeg, png, gif.',
        'avatar.max' => 'Avatar không được vượt quá 2MB.',
    ]);

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->birthday = $validated['birthday'] ?? null;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return response()->json($user);
    }
}
