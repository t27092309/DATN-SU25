<?php

use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\API\Admin\ProductController as AdminProductController;
use App\Http\Controllers\API\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Api\Admin\ScentGroupController as AdminScentGroupController;
use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Client\CartItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Client\ProductController as ClientProductController;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Controllers\API\Client\UserProfileController;
use App\Http\Controllers\API\Client\UserAddressController;
use App\Http\Controllers\API\Client\LocationController;

Route::middleware([CorsMiddleware::class])->group(function () {

    // route admin yêu cầu xác thực
    Route::middleware('auth:sanctum')->group(function () {

        // route giỏ hàng cho client
        Route::apiResource('cart-items', CartItemController::class);

        // Route đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);

        // Hồ sơ người dùng
        Route::get('/user/profile', [UserProfileController::class, 'show']);
        Route::put('/user/profile', [UserProfileController::class, 'update']);

        // Địa chỉ người dùng
        Route::get('/user/addresses', [UserAddressController::class, 'index']);
        Route::post('/user/addresses', [UserAddressController::class, 'store']);
        Route::put('/user/addresses/{id}', [UserAddressController::class, 'update']);
        Route::delete('/user/addresses/{id}', [UserAddressController::class, 'destroy']);
        Route::put('/user/addresses/{id}/set-default', [UserAddressController::class, 'setDefault']);

        // Chọn địa chỉ
        Route::get('/provinces', [LocationController::class, 'provinces']);
        Route::get('/provinces/{province_code}/districts', [LocationController::class, 'districts']);
        Route::get('/districts/{district_code}/wards', [LocationController::class, 'wards']);

        // Route admin (yêu cầu quyền admin:full-access)
        Route::middleware('ability:admin:full-access')->prefix('admin')->group(function () {
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('coupons', AdminCouponController::class);
            Route::apiResource('brands', AdminBrandController::class);
             // Soft Delete product 
            Route::get('products/trashed', [AdminProductController::class, 'trashed']);
            Route::put('products/{id}/restore', [AdminProductController::class, 'restore']);
            Route::delete('products/{id}/force', [AdminProductController::class, 'forceDelete']);
            Route::apiResource('products', AdminProductController::class);
            // Route upload ảnh chính
            Route::post('products/{product}/image', [AdminProductController::class, 'uploadImage']);
            // Route upload ảnh phụ
            Route::post('products/{product}/images', [AdminProductController::class, 'uploadImages']);
            Route::delete('images/{imageId}', [AdminProductController::class, 'deleteImage']);
            Route::get('product-variants/trashed', [AdminProductVariantController::class, 'trashed']);
            Route::put('product-variants/restore/{id}', [AdminProductVariantController::class, 'restore']);
            Route::apiResource('product-variants', AdminProductVariantController::class);
            Route::apiResource('scent-groups', AdminScentGroupController::class);
        });
    });


    //Route xác thực
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    //Route test
    Route::get('/hello', function () {
        return response()->json([
            'message' => 'Hello from Laravel with manual CORS!',
        ]);
    });


    // route client
    Route::get('most-viewed-products-by-categories', [ClientProductController::class, 'getMostViewedProductsByCategories']);
    Route::get('category-page-products', [ClientProductController::class, 'getCategoryPageProducts']);
    //route chi tiết sản phẩm cliend
    Route::get('/detailproducts/{slug}', [ClientProductController::class, 'ShowBySlug']);
    //test postman:   http://localhost:8000/api/detailproducts/đường dẫn slug
    Route::get('/products/search', [ClientProductController::class, 'search']);
});

// use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello from Laravel with manual CORS!',
//     ]);
// });
