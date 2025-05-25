<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductVariantController;


Route::middleware([CorsMiddleware::class])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/hello', function () {
        return response()->json([
            'message' => 'Hello from Laravel with manual CORS!',
        ]);
    });
});

// use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello from Laravel with manual CORS!',
//     ]);
// });

Route::apiResource('products', ProductController::class);
Route::get('product-variants/trashed', [ProductVariantController::class, 'trashed']);
Route::put('product-variants/restore/{id}', [ProductVariantController::class, 'restore']);
Route::apiResource('product-variants', ProductVariantController::class);

