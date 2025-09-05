<?php

namespace App\Http\Controllers\API\Client;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\PasswordChangeToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getProfile()
    {
        return Auth::user();
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date',
        ]);

        try {
            $data = $request->only(['name', 'phone_number', 'email', 'gender', 'birthday']);
            // Chuyển chuỗi rỗng sang null để tránh lỗi DB
            foreach ($data as $key => $value) {
                if ($value === '') $data[$key] = null;
            }

            if ($request->hasFile('avatar')) {
                // Xóa avatar cũ nếu có để tránh lãng phí dung lượng
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }

                // Lưu avatar mới và lấy đường dẫn tương đối
                $path = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $path;
            }

            $user->update($data);

            return response()->json([
                'message' => 'Cập nhật thông tin thành công',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi cập nhật profile: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật'], 500);
        }
    }


    public function requestChangePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 400);
        }

        User::where('id', $user->id)->update(['password' => bcrypt($request->new_password)]);

        Mail::send('emails.password_changed', ['user' => $user], function ($m) use ($user) {
            $m->to($user->email)->subject('Mật khẩu đã được thay đổi');
        });

        return response()->json(['message' => 'Đổi mật khẩu thành công. Vui lòng kiểm tra email.']);
    }



    public function confirmChangePassword($token)
    {
        $record = PasswordChangeToken::where('token', $token)->first();
        if (!$record) {
            return response()->json(['message' => 'Token không hợp lệ'], 400);
        }

        $user = User::find($record->user_id);
        $user->update(['password' => $record->new_password]);
        $record->delete();

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }
}
