<?php

use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\API\Admin\ProductController as AdminProductController;
use App\Http\Controllers\API\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Api\Admin\ScentGroupController as AdminScentGroupController;
use App\Http\Controllers\API\Admin\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Client\ProductController as ClientProductController;
use App\Http\Middleware\CorsMiddleware;


Route::middleware([CorsMiddleware::class])->group(function () {

    // route admin yêu cầu xác thực
    Route::middleware('auth:sanctum')->group(function () {
        // Route đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);

        // Route admin (yêu cầu quyền admin:full-access)
        Route::middleware('ability:admin:full-access')->prefix('admin')->group(function () {
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('coupons', AdminCouponController::class);
            Route::apiResource('brands', AdminBrandController::class);
            Route::apiResource('products', AdminProductController::class);
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
    Route::get('/products/{id}', [ClientProductController::class, 'getdetailproducts']);
    //test postman:  http://127.0.0.1:8000/api/products/31
});

// use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello from Laravel with manual CORS!',
//     ]);
// });
