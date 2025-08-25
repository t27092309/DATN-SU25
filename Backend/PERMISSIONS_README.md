# Hệ thống Phân quyền Admin

## Tổng quan

Hệ thống phân quyền được xây dựng với các thành phần chính:

### Backend (Laravel)
- **Models**: `User`, `Role` với quan hệ many-to-many
- **Middleware**: `CheckPermission` để kiểm tra quyền
- **Controllers**: `RoleController` để quản lý roles và permissions
- **Database**: Bảng `roles`, `role_user`, trường `role` trong `users`

### Frontend (Vue.js)
- **Store**: `permissions.js` để quản lý trạng thái quyền
- **Components**: `PermissionGuard.vue` để ẩn/hiện nội dung
- **Pages**: `RoleManager.vue` để quản lý roles

## Cài đặt

### 1. Chạy Migration
```bash
php artisan migrate
```

### 2. Chạy Seeder
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Khởi động ứng dụng
```bash
# Backend
php artisan serve

# Frontend
npm run dev
```

## Sử dụng

### Backend

#### 1. Kiểm tra quyền trong Controller
```php
public function index()
{
    $user = auth()->user();
    
    if (!$user->hasPermission('products:view')) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    
    // Logic xử lý
}
```

#### 2. Sử dụng Middleware trong Routes
```php
Route::middleware('permission:products:create')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});
```

#### 3. Kiểm tra quyền trong Blade Templates
```php
@if(auth()->user()->hasPermission('products:edit'))
    <button>Edit Product</button>
@endif
```

### Frontend

#### 1. Sử dụng PermissionGuard Component
```vue
<template>
  <PermissionGuard permission="products:create">
    <button @click="createProduct">Tạo sản phẩm mới</button>
  </PermissionGuard>
  
  <PermissionGuard permissions="['products:edit', 'products:delete']" requireAll="false">
    <div>Bạn có thể chỉnh sửa hoặc xóa sản phẩm</div>
  </PermissionGuard>
</template>

<script setup>
import PermissionGuard from '@/components/common/PermissionGuard.vue';
</script>
```

#### 2. Sử dụng Permissions Store
```vue
<script setup>
import { usePermissionsStore } from '@/stores/permissions';

const permissionsStore = usePermissionsStore();

// Kiểm tra quyền đơn lẻ
if (permissionsStore.hasPermission('products:create')) {
  // Hiển thị nút tạo sản phẩm
}

// Kiểm tra nhiều quyền (có ít nhất 1)
if (permissionsStore.hasAnyPermission(['products:edit', 'products:delete'])) {
  // Hiển thị menu thao tác
}

// Kiểm tra tất cả quyền
if (permissionsStore.hasAllPermissions(['products:view', 'products:edit'])) {
  // Hiển thị giao diện đầy đủ
}

// Kiểm tra quyền theo module
if (permissionsStore.canView('products')) {
  // Hiển thị danh sách sản phẩm
}

if (permissionsStore.canCreate('products')) {
  // Hiển thị nút tạo mới
}
</script>
```

#### 3. Quản lý Roles
Truy cập `/admin/roles` để quản lý roles và permissions.

## Cấu trúc Permissions

### Format: `module:action`

#### Modules chính:
- `dashboard`: Bảng điều khiển
- `products`: Sản phẩm
- `categories`: Danh mục
- `brands`: Thương hiệu
- `orders`: Đơn hàng
- `users`: Người dùng
- `reports`: Báo cáo
- `settings`: Cài đặt
- `coupons`: Mã giảm giá
- `banners`: Banner
- `shipping`: Vận chuyển
- `inventory`: Kho hàng
- `roles`: Vai trò

#### Actions:
- `view`: Xem
- `create`: Tạo mới
- `edit`: Chỉnh sửa
- `delete`: Xóa

### Ví dụ Permissions:
- `products:view` - Xem danh sách sản phẩm
- `products:create` - Tạo sản phẩm mới
- `products:edit` - Chỉnh sửa sản phẩm
- `products:delete` - Xóa sản phẩm

## Roles mặc định

### 1. Super Admin
- Có tất cả quyền
- Quản lý toàn bộ hệ thống

### 2. Manager
- Quản lý sản phẩm và đơn hàng
- Không có quyền quản lý users và roles

### 3. Staff
- Xem và chỉnh sửa cơ bản
- Không có quyền tạo mới hoặc xóa

### 4. Viewer
- Chỉ có quyền xem dữ liệu
- Không có quyền chỉnh sửa

## API Endpoints

### Quản lý Roles
- `GET /api/admin/roles` - Lấy danh sách roles
- `POST /api/admin/roles` - Tạo role mới
- `PUT /api/admin/roles/{id}` - Cập nhật role
- `DELETE /api/admin/roles/{id}` - Xóa role
- `GET /api/admin/roles/permissions` - Lấy danh sách permissions có sẵn

### Quản lý User Permissions
- `GET /api/admin/user/permissions` - Lấy permissions của user hiện tại
- `POST /api/admin/roles/assign` - Gán role cho user
- `POST /api/admin/roles/remove` - Gỡ role khỏi user

## Bảo mật

### 1. Middleware Protection
Tất cả API admin đều được bảo vệ bởi middleware `ability:admin:full-access`

### 2. Permission Validation
Mỗi action quan trọng đều được kiểm tra quyền trước khi thực hiện

### 3. Frontend Protection
- Navigation guards kiểm tra quyền truy cập
- Components ẩn/hiện dựa trên quyền
- Store quản lý trạng thái quyền

## Mở rộng

### Thêm Permission mới
1. Thêm permission vào `RoleController::getAvailablePermissions()`
2. Cập nhật seeder nếu cần
3. Sử dụng trong components và controllers

### Thêm Role mới
1. Tạo role trong seeder
2. Gán permissions phù hợp
3. Test quyền truy cập

### Thêm Module mới
1. Định nghĩa permissions cho module
2. Tạo middleware nếu cần
3. Cập nhật frontend components

