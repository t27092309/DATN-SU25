<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('users')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        $role->load('users');
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $role->update($validated);
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function assignToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::find($validated['user_id']);
        $role = Role::find($validated['role_id']);

        $user->roles()->syncWithoutDetaching([$role->id]);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function removeFromUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::find($validated['user_id']);
        $user->roles()->detach($validated['role_id']);

        return response()->json(['message' => 'Role removed successfully']);
    }

    public function getAvailablePermissions()
    {
        $permissions = [
            'dashboard:view',
            'products:view', 'products:create', 'products:edit', 'products:delete',
            'categories:view', 'categories:create', 'categories:edit', 'categories:delete',
            'brands:view', 'brands:create', 'brands:edit', 'brands:delete',
            'orders:view', 'orders:edit', 'orders:delete',
            'users:view', 'users:create', 'users:edit', 'users:delete',
            'reports:view',
            'settings:view', 'settings:edit',
            'coupons:view', 'coupons:create', 'coupons:edit', 'coupons:delete',
            'banners:view', 'banners:create', 'banners:edit', 'banners:delete',
            'shipping:view', 'shipping:create', 'shipping:edit', 'shipping:delete',
            'inventory:view', 'inventory:edit'
        ];

        return response()->json($permissions);
    }
}

