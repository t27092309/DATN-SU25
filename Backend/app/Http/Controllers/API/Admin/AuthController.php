<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', 'string', 'in:user,admin'],
        ], [
            'name.required' => 'Tên không được để trống.',
            'name.string' => 'Tên phải là chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',

            'email.required' => 'Email không được để trống.',
            'email.string' => 'Email phải là chuỗi ký tự.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',

            'password.required' => 'Mật khẩu không được để trống.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

            'role.string' => 'Vai trò phải là chuỗi.',
            'role.in' => 'Vai trò không hợp lệ.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
        ]);

        $token = $user->createToken('auth_token', [$user->role === 'admin' ? 'admin:full-access' : 'user'])->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'remember.boolean' => 'Trường ghi nhớ phải là giá trị đúng hoặc sai.',
        ]);

        // Thử đăng nhập bằng session để tận dụng remember_token (nếu có dùng web)
        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return response()->json(['message' => 'Thông tin đăng nhập không chính xác.'], 401);
        }

        $user = Auth::user();

        // Xác định quyền truy cập theo vai trò
        $abilities = $user->role === 'admin' ? ['admin:full-access'] : ['user'];

        // Tạo token API với Sanctum
        $tokenResult = $user->createToken('auth_token', $abilities);
        $plainTextToken = $tokenResult->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $plainTextToken,
            'remember' => $request->boolean('remember'), // Trả lại để frontend biết
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đăng xuất thành công.']);
    }
}
