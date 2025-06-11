<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRoleRequest;

class UserController extends Controller
{
    /**
     * Cập nhật vai trò của một user cụ thể.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        // 1. Admin không thể thay đổi vai trò của chính mình
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'Bạn không thể thay đổi vai trò của chính mình.',
            ], 403); // Forbidden
        }

        // 2. Admin không thể thay đổi vai trò của một admin khác
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Bạn không thể thay đổi vai trò của một quản trị viên khác.',
            ], 403); // Forbidden
        }

        // Cập nhật role
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Vai trò của người dùng đã được cập nhật thành công.',
            'user' => $user,
        ]);
    }
}