<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ScentGroupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductVariantController;


Route::middleware([CorsMiddleware::class])->group(function () {
    // route admin
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('coupons', CouponController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('products', ProductController::class);
    Route::get('product-variants/trashed', [ProductVariantController::class, 'trashed']);
    Route::put('product-variants/restore/{id}', [ProductVariantController::class, 'restore']);
    Route::apiResource('product-variants', ProductVariantController::class);
    Route::apiResource('scent-groups', ScentGroupController::class);

    Route::get('/hello', function () {
        return response()->json([
            'message' => 'Hello from Laravel with manual CORS!',
        ]);
    });

    Route::get('most-viewed-products-by-categories', [ProductController::class, 'getMostViewedProductsByCategories']);
});

// use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello from Laravel with manual CORS!',
//     ]);
// });
