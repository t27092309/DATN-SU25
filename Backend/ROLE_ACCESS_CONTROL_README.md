# Hệ thống Kiểm soát Quyền Truy cập theo Role

## Tổng quan

Hệ thống đã được cập nhật để kiểm soát quyền truy cập dựa trên role của user:

### 🔒 **Quyền truy cập Admin**
- **Admin & Staff**: Có thể truy cập trang admin
- **User thường**: Không thể truy cập trang admin

### 🛒 **Quyền truy cập User Features**
- **User thường**: Có thể sử dụng các chức năng mua hàng, quản lý tài khoản
- **Admin & Staff**: Không thể sử dụng các chức năng user thường

## Cấu trúc Role

### 1. **User (Customer)**
- **Quyền truy cập**: Trang mua hàng, quản lý tài khoản cá nhân
- **Không thể**: Truy cập trang admin
- **Chức năng**: Mua sản phẩm, xem đơn hàng, quản lý profile

### 2. **Staff**
- **Quyền truy cập**: Trang admin (quản lý sản phẩm, đơn hàng)
- **Không thể**: Sử dụng chức năng mua hàng của user thường
- **Chức năng**: Quản lý sản phẩm, đơn hàng, báo cáo cơ bản

### 3. **Admin**
- **Quyền truy cập**: Tất cả trang admin
- **Không thể**: Sử dụng chức năng mua hàng của user thường
- **Chức năng**: Quản lý toàn bộ hệ thống, users, roles

## Frontend Implementation

### 1. **Navigation Guards**
```javascript
// router/index.js
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const user = authStore.user;

  // Kiểm tra truy cập admin
  if (to.path.startsWith("/admin")) {
    if (!user) {
      next('/login');
      return;
    }
    
    if (user.role === 'user') {
      next('/'); // Chuyển về trang chủ
      return;
    }
    
    if (user.role === 'admin' || user.role === 'staff') {
      next();
      return;
    }
  }

  // Kiểm tra truy cập user features
  if (!to.path.startsWith("/admin") && to.path !== "/login" && to.path !== "/register") {
    if (user && (user.role === 'admin' || user.role === 'staff')) {
      next('/admin/dashboard'); // Chuyển về admin dashboard
      return;
    }
  }

  next();
});
```

### 2. **Conditional Rendering**
```vue
<!-- ClientLayout.vue -->
<template>
  <!-- Menu cho user thường -->
  <router-link v-if="!isAdminOrStaff" to="/order-history">
    Tra cứu lịch sử mua hàng
  </router-link>
  
  <!-- Menu cho admin/staff -->
  <router-link v-if="isAdminOrStaff" to="/admin/dashboard">
    Quản lý Admin
  </router-link>
</template>

<script setup>
const isAdminOrStaff = computed(() => {
  return authStore.user && (authStore.user.role === 'admin' || authStore.user.role === 'staff');
});
</script>
```

### 3. **User Display Component**
```vue
<!-- UserDisplay.vue -->
<template>
  <!-- Menu cho user thường -->
  <template v-if="authStore.user?.role === 'user'">
    <router-link to="/tai-khoan">Tài khoản của tôi</router-link>
    <router-link to="/tai-khoan/don-hang">Đơn mua</router-link>
  </template>
  
  <!-- Menu cho admin/staff -->
  <template v-if="authStore.user?.role === 'admin' || authStore.user?.role === 'staff'">
    <router-link to="/admin/dashboard">Quản lý Admin</router-link>
    <div>Vai trò: {{ getRoleDisplayName(authStore.user?.role) }}</div>
  </template>
</template>
```

## Backend Implementation

### 1. **Middleware cho Admin**
```php
// CheckAdminRole.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Chỉ cho phép admin và staff truy cập
    if ($user->role !== 'admin' && $user->role !== 'staff') {
        return response()->json(['message' => 'Forbidden - Access denied'], 403);
    }

    return $next($request);
}
```

### 2. **Middleware cho User Features**
```php
// CheckUserRole.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Chỉ cho phép user thường truy cập
    if ($user->role === 'admin' || $user->role === 'staff') {
        return response()->json(['message' => 'Forbidden - Admin/Staff cannot access user features'], 403);
    }

    return $next($request);
}
```

### 3. **Route Configuration**
```php
// routes/api.php

// Admin routes (chỉ admin/staff)
Route::middleware(['ability:admin:full-access', 'admin.role'])->prefix('admin')->group(function () {
    Route::apiResource('users', \App\Http\Controllers\API\Admin\UserController::class);
    Route::apiResource('products', AdminProductController::class);
    // ... other admin routes
});

// User routes (chỉ user thường)
Route::middleware('user.role')->group(function () {
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::post('/user/update-profile', [UserController::class, 'updateProfile']);
    // ... other user routes
});
```

## Cách sử dụng

### 1. **Đăng ký Middleware**
```php
// bootstrap/app.php
$middleware->alias([
    'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
    'user.role' => \App\Http\Middleware\CheckUserRole::class,
]);
```

### 2. **Áp dụng cho Routes**
```php
// Admin routes
Route::middleware(['auth:sanctum', 'admin.role'])->group(function () {
    // Admin only routes
});

// User routes
Route::middleware(['auth:sanctum', 'user.role'])->group(function () {
    // User only routes
});
```

### 3. **Kiểm tra trong Controller**
```php
public function someMethod(Request $request)
{
    $user = $request->user();
    
    if ($user->role === 'user') {
        // Logic cho user thường
    } elseif ($user->role === 'admin' || $user->role === 'staff') {
        // Logic cho admin/staff
    }
}
```

## Bảo mật

### 1. **Frontend Protection**
- Navigation guards kiểm tra role trước khi chuyển trang
- Conditional rendering ẩn/hiện menu phù hợp
- Redirect tự động khi truy cập sai trang

### 2. **Backend Protection**
- Middleware kiểm tra role ở tầng API
- Validation trong controllers
- Response codes phù hợp (401, 403)

### 3. **Database Protection**
- Role được lưu trong database
- Không thể bypass qua frontend

## Testing

### 1. **Test Admin Access**
```bash
# Login với user có role 'user'
# Truy cập /admin/dashboard
# Expected: Redirect về trang chủ
```

### 2. **Test User Features**
```bash
# Login với user có role 'admin'
# Truy cập /tai-khoan
# Expected: Redirect về /admin/dashboard
```

### 3. **Test API Protection**
```bash
# User role 'user' gọi API admin
curl -H "Authorization: Bearer {token}" /api/admin/users
# Expected: 403 Forbidden
```

## Lưu ý

1. **Session Management**: Đảm bảo role được cập nhật khi user thay đổi
2. **Cache**: Clear cache khi thay đổi role
3. **Logging**: Log tất cả thay đổi role và truy cập
4. **Error Handling**: Xử lý lỗi gracefully khi middleware fail

## Mở rộng

### Thêm Role mới
1. Cập nhật middleware
2. Cập nhật frontend logic
3. Cập nhật database seeder
4. Test quyền truy cập

### Thêm Permission mới
1. Định nghĩa permission trong RoleController
2. Cập nhật PermissionGuard component
3. Test với các role khác nhau
