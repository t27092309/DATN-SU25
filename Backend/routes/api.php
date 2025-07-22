<?php

use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\API\Admin\ProductController as AdminProductController;
use App\Http\Controllers\API\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Api\Admin\AttributeController as AttributeController;
use App\Http\Controllers\Api\Admin\AttributeValueController as AttributeValueController;
use App\Http\Controllers\Api\Admin\ScentGroupController as AdminScentGroupController;
use App\Http\Controllers\Api\Admin\ShippingMethodController as AdminShippingMethodController;
use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Admin\OrderController;
use App\Http\Controllers\API\Client\CartItemController;
use App\Http\Controllers\API\Client\CheckoutController;
use App\Http\Controllers\API\Client\OrderController as ClientOrderController;
use App\Http\Controllers\API\Client\PaymentMethodController as ClientPaymentMethodController;
use App\Http\Controllers\API\Client\ProductVariantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Client\ProductController as ClientProductController;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Controllers\API\Client\UserProfileController;
use App\Http\Controllers\API\Client\UserAddressController;
use App\Http\Controllers\API\Client\LocationController;
use App\Http\Controllers\API\Client\PaymentController;

Route::middleware([CorsMiddleware::class])->group(function () {

    // route admin yêu cầu xác thực
    Route::middleware('auth:sanctum')->group(function () {

        // route giỏ hàng cho client
        Route::apiResource('cart-items', CartItemController::class);
        Route::delete('cart-items/clear-selected', [CartItemController::class, 'clearSelected']);

        // route thanh toan cho Client
        Route::get('/coupons/available', [CheckoutController::class, 'getAvailableCoupons']);
        Route::get('/shipping-methods', [CheckoutController::class, 'getActiveShippingMethods']);
        Route::post('checkout/order-items', [CheckoutController::class, 'getCheckoutItems']);
        Route::post('checkout/place-order', [CheckoutController::class, 'placeOrder']);
        Route::get('/payment-methods', [ClientPaymentMethodController::class, 'index']);
        Route::post('checkout/buy-now', [CheckoutController::class, 'buyNow']);
        Route::post('/check-coupon', [CheckoutController::class, 'checkCoupon']);
        Route::get('product-variants/{id}', [ProductVariantController::class, 'show']);

        // Payment API (public, no auth required)
        Route::post('/payment/create', [PaymentController::class, 'createPayment']);
        Route::get('/payment/vnpay-callback', [PaymentController::class, 'handleVnpayCallback']);
        Route::post('/payment/momo-callback', [PaymentController::class, 'handleMomoCallback']);


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

        // Theo dõi đơn hàng
        Route::prefix('orders')->group(function () {
            Route::get('/counts', [ClientOrderController::class, 'getOrderCounts']);
            Route::get('/', [ClientOrderController::class, 'index']);
            Route::get('{order}', [ClientOrderController::class, 'show']);
            Route::post('/{order}/mark-delivered', [ClientOrderController::class, 'markAsDelivered']);
            Route::post('/{order}/cancel', [ClientOrderController::class, 'cancelOrder']);
        });

        // Route admin (yêu cầu quyền admin:full-access)
        Route::middleware('ability:admin:full-access')->prefix('admin')->group(function () {
            //route quản lí đơn hàng bên admin
            Route::prefix('orders')->controller(OrderController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('{order}', 'show');
                Route::patch('{order}/status', 'updateStatus');
                Route::put('{order}/note', 'updateNote');
                Route::get('{order}/payments', 'getPayments');
            });

            Route::apiResource('shipping-methods', AdminShippingMethodController::class);

            //categories
            Route::get('categories/trashed', [AdminCategoryController::class, 'trashed']);
            Route::put('categories/{id}/restore', [AdminCategoryController::class, 'restore']);
            Route::delete('categories/{id}/force', [AdminCategoryController::class, 'forceDelete']);
            Route::apiResource('categories', AdminCategoryController::class);

            // coupons
            Route::get('coupons/trashed', [AdminCouponController::class, 'trashed']);
            Route::put('coupons/{id}/restore', [AdminCouponController::class, 'restore']);
            Route::delete('coupons/{id}/force', [AdminCouponController::class, 'forceDelete']);
            Route::apiResource('coupons', AdminCouponController::class);

            // brands
            Route::apiResource('brands', AdminBrandController::class);
            Route::get('brands/trashed', [AdminBrandController::class, 'trashed']);
            Route::post('brands/{id}/restore', [AdminBrandController::class, 'restore']);
            Route::delete('brands/{id}/force', [AdminBrandController::class, 'forceDelete']);
            Route::post('upload-image', [AdminBrandController::class, 'uploadImage']);

            // Soft Delete product
            Route::get('products/trashed', [AdminProductController::class, 'trashed'])->name('products.trashed');
            Route::post('products/{id}/restore', [AdminProductController::class, 'restore'])->name('products.restore');
            Route::delete('products/{id}/force-delete', [AdminProductController::class, 'forceDelete'])->name('products.forceDelete');
            Route::apiResource('products', AdminProductController::class);
            // Route upload ảnh chính
            Route::post('products/{product}/image', [AdminProductController::class, 'uploadImage']);
            // Route upload ảnh phụ
            Route::post('products/{product}/images', [AdminProductController::class, 'uploadImages']);
            Route::delete('images/{imageId}', [AdminProductController::class, 'deleteImage']);

            Route::get('product-variants/trashed', [AdminProductVariantController::class, 'trashed']);
            Route::put('product-variants/restore/{id}', [AdminProductVariantController::class, 'restore']);
            Route::apiResource('product-variants', AdminProductVariantController::class);

            Route::post('products/{product}/variants/generate', [AdminProductVariantController::class, 'generateForProduct'])
                ->name('admin.products.variants.generate');

            Route::apiResource('attributes', AttributeController::class);
            Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'indexByAttribute'])
                ->name('admin.attributes.values.index');
            Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'storeByAttribute']);
            Route::put('attributes/{attribute}/values/{attributeValue}', [AttributeValueController::class, 'updateNested']);
            Route::delete('attributes/{attribute}/values/{attributeValue}', [AttributeValueController::class, 'destroyNested']);

            Route::apiResource('attribute-values', AttributeValueController::class);

            // Nhóm scent-groups
            Route::get('scent-groups/trashed', [AdminScentGroupController::class, 'trashed']);
            Route::put('scent-groups/{id}/restore', [AdminScentGroupController::class, 'restore']);
            Route::delete('scent-groups/{id}/force', [AdminScentGroupController::class, 'forceDelete']);
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

Route::middleware([CorsMiddleware::class])->post('admin/upload-image', [AdminBrandController::class, 'uploadImage']);
