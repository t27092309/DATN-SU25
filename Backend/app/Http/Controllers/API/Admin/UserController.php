<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->load('roles', 'addresses');
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|string|in:user,admin,staff',
        ]);

        $user->update($validated);
        
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:user,admin,staff',
        ]);

        $user->update(['role' => $validated['role']]);
        
        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getUsersByRole(Request $request)
    {
        $role = $request->query('role', 'user');
        
        $users = User::where('role', $role)
            ->with('roles')
            ->paginate(15);
            
        return response()->json($users);
    }

    public function getUsersStats()
    {
        $stats = [
            'total' => User::count(),
            'customers' => User::where('role', 'user')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];
        
        return response()->json($stats);
    }
}
