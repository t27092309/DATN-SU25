<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo role Admin với tất cả quyền
        Role::create([
            'name' => 'Super Admin',
            'description' => 'Quản trị viên cấp cao với tất cả quyền hạn',
            'permissions' => [
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
                'inventory:view', 'inventory:edit',
                'roles:view', 'roles:create', 'roles:edit', 'roles:delete'
            ]
        ]);

        // Tạo role Manager với quyền quản lý sản phẩm và đơn hàng
        Role::create([
            'name' => 'Manager',
            'description' => 'Quản lý với quyền quản lý sản phẩm và đơn hàng',
            'permissions' => [
                'dashboard:view',
                'products:view', 'products:create', 'products:edit',
                'categories:view', 'categories:create', 'categories:edit',
                'brands:view', 'brands:create', 'brands:edit',
                'orders:view', 'orders:edit',
                'reports:view',
                'coupons:view', 'coupons:create', 'coupons:edit',
                'banners:view', 'banners:create', 'banners:edit',
                'shipping:view', 'shipping:create', 'shipping:edit',
                'inventory:view', 'inventory:edit'
            ]
        ]);

        // Tạo role Staff với quyền xem và chỉnh sửa cơ bản
        Role::create([
            'name' => 'Staff',
            'description' => 'Nhân viên với quyền xem và chỉnh sửa cơ bản',
            'permissions' => [
                'dashboard:view',
                'products:view', 'products:edit',
                'categories:view',
                'brands:view',
                'orders:view', 'orders:edit',
                'reports:view',
                'coupons:view',
                'banners:view',
                'shipping:view',
                'inventory:view'
            ]
        ]);

        // Tạo role Viewer chỉ có quyền xem
        Role::create([
            'name' => 'Viewer',
            'description' => 'Người dùng chỉ có quyền xem dữ liệu',
            'permissions' => [
                'dashboard:view',
                'products:view',
                'categories:view',
                'brands:view',
                'orders:view',
                'reports:view',
                'coupons:view',
                'banners:view',
                'shipping:view',
                'inventory:view'
            ]
        ]);
    }
}
