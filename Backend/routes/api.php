<?php

use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\CorsMiddleware;



Route::middleware([CorsMiddleware::class])->group(function () {
     Route::apiResource('categories', CategoryController::class);

    Route::apiResource('coupons', CouponController::class);

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

