<?php

use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\API\Admin\ProductController as AdminProductController;
use App\Http\Controllers\API\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Api\Admin\ScentGroupController as AdminScentGroupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Client\ProductController as ClientProductController;
use App\Http\Middleware\CorsMiddleware;


Route::middleware([CorsMiddleware::class])->group(function () {
    // route admin
    Route::apiResource('categories', AdminCategoryController::class);
    Route::apiResource('coupons', AdminCouponController::class);
    Route::apiResource('brands', AdminBrandController::class);
    Route::apiResource('products', AdminProductController::class);
    Route::get('product-variants/trashed', [AdminProductVariantController::class, 'trashed']);
    Route::put('product-variants/restore/{id}', [AdminProductVariantController::class, 'restore']);
    Route::apiResource('product-variants', AdminProductVariantController::class);
    Route::apiResource('scent-groups', AdminScentGroupController::class);

    Route::get('/hello', function () {
        return response()->json([
            'message' => 'Hello from Laravel with manual CORS!',
        ]);
    });

    // route client
    Route::get('most-viewed-products-by-categories', [ClientProductController::class, 'getMostViewedProductsByCategories']);
    Route::get('category-page-products', [ClientProductController::class, 'getCategoryPageProducts']);

});

// use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello from Laravel with manual CORS!',
//     ]);
// });
