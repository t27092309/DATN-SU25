# Quản lý Người dùng trong Admin

## Tổng quan

Chức năng quản lý người dùng cho phép admin:
- Xem danh sách tất cả người dùng
- Thay đổi vai trò của người dùng (Customer ↔ Staff ↔ Admin)
- Xem thống kê người dùng theo vai trò
- Tìm kiếm và lọc người dùng
- Xem chi tiết thông tin người dùng

## Cài đặt

### 1. Chạy Migration
```bash
php artisan migrate
```

### 2. Chạy Seeder
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Truy cập trang quản lý
```
http://localhost:3000/admin/users
```

## Tính năng

### 1. Thống kê Người dùng
- **Tổng số**: Tổng số người dùng trong hệ thống
- **Khách hàng**: Số người dùng có role "user"
- **Nhân viên**: Số người dùng có role "staff"
- **Admin**: Số người dùng có role "admin"

### 2. Bộ lọc và Tìm kiếm
- **Tìm kiếm**: Theo tên hoặc email người dùng
- **Lọc theo vai trò**: Tất cả, Khách hàng, Nhân viên, Admin

### 3. Quản lý Vai trò
- **Thay đổi vai trò**: Dropdown để chọn vai trò mới
- **Không thể tự thay đổi**: Admin không thể thay đổi vai trò của chính mình
- **Cập nhật real-time**: Thống kê được cập nhật ngay lập tức

### 4. Thông tin Chi tiết
- **Thông tin cơ bản**: Tên, email, avatar
- **Vai trò hiện tại**: Role hiện tại của người dùng
- **Ngày tham gia**: Thời gian tạo tài khoản
- **Địa chỉ**: Danh sách địa chỉ của người dùng

## API Endpoints

### Quản lý Users
- `GET /api/admin/users` - Lấy danh sách users (có phân trang)
- `GET /api/admin/users/{id}` - Lấy thông tin chi tiết user
- `PUT /api/admin/users/{id}` - Cập nhật thông tin user
- `DELETE /api/admin/users/{id}` - Xóa user

### Quản lý Role
- `PATCH /api/admin/users/{id}/role` - Cập nhật role của user
- `GET /api/admin/users/by-role?role=staff` - Lấy users theo role
- `GET /api/admin/users/stats` - Lấy thống kê users

### Ví dụ Request/Response

#### Cập nhật Role
```bash
PATCH /api/admin/users/1/role
Content-Type: application/json
Authorization: Bearer {token}

{
  "role": "staff"
}
```

Response:
```json
{
  "message": "User role updated successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "staff",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

#### Lấy Thống kê
```bash
GET /api/admin/users/stats
Authorization: Bearer {token}
```

Response:
```json
{
  "total": 100,
  "customers": 80,
  "staff": 15,
  "admins": 5
}
```

## Vai trò và Quyền hạn

### 1. Customer (user)
- Mua sản phẩm
- Xem đơn hàng cá nhân
- Quản lý thông tin cá nhân

### 2. Staff
- Xem và chỉnh sửa sản phẩm
- Quản lý đơn hàng
- Xem báo cáo cơ bản
- Không thể quản lý users và roles

### 3. Admin
- Tất cả quyền của Staff
- Quản lý users và roles
- Quản lý toàn bộ hệ thống
- Xem tất cả báo cáo

## Bảo mật

### 1. Middleware Protection
- Tất cả API đều được bảo vệ bởi `ability:admin:full-access`
- Chỉ admin mới có thể truy cập

### 2. Validation
- Role phải là một trong: `user`, `staff`, `admin`
- Email phải unique khi cập nhật
- Không thể xóa chính mình

### 3. Frontend Protection
- Kiểm tra quyền trước khi hiển thị chức năng
- Disable nút thay đổi role cho chính mình
- Confirm dialog khi xóa user

## Sử dụng trong Code

### Backend (Laravel)

#### Kiểm tra quyền trong Controller
```php
public function updateUserRole(Request $request, User $user)
{
    // Kiểm tra quyền
    if (!auth()->user()->hasPermission('users:edit')) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    
    $validated = $request->validate([
        'role' => 'required|string|in:user,admin,staff',
    ]);

    $user->update(['role' => $validated['role']]);
    
    return response()->json([
        'message' => 'User role updated successfully',
        'user' => $user->load('roles')
    ]);
}
```

#### Middleware trong Routes
```php
Route::middleware('permission:users:view')->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});
```

### Frontend (Vue.js)

#### Sử dụng PermissionGuard
```vue
<template>
  <PermissionGuard permission="users:edit">
    <button @click="updateUserRole">Cập nhật vai trò</button>
  </PermissionGuard>
</template>
```

#### Kiểm tra quyền trong Script
```javascript
import { usePermissionsStore } from '@/stores/permissions';

const permissionsStore = usePermissionsStore();

if (permissionsStore.hasPermission('users:delete')) {
  // Hiển thị nút xóa user
}
```

## Lưu ý

### 1. Bảo mật
- Không thể tự thay đổi vai trò của chính mình
- Cần confirm khi xóa user
- Log tất cả thay đổi vai trò

### 2. Performance
- Sử dụng pagination cho danh sách users
- Cache thống kê nếu cần
- Lazy load thông tin chi tiết

### 3. UX/UI
- Loading states khi thay đổi role
- Thông báo thành công/lỗi
- Responsive design cho mobile

## Mở rộng

### Thêm Role mới
1. Cập nhật validation trong UserController
2. Thêm option trong dropdown
3. Cập nhật thống kê
4. Test quyền truy cập

### Thêm thông tin User
1. Cập nhật migration
2. Thêm field trong form
3. Cập nhật API
4. Hiển thị trong UI

### Thêm tính năng
1. Bulk actions (thay đổi role hàng loạt)
2. Export users
3. Import users
4. User activity log
