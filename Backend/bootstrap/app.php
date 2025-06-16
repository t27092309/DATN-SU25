<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use App\Http\Middleware\CorsMiddleware; // Đảm bảo dòng này tồn tại

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Áp dụng Sanctum's EnsureFrontendRequestsAreStateful cho API routes
        // Đây là middleware quan trọng để Sanctum hoạt động đúng với SPA (xử lý CSRF token)
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'ability' => CheckAbilities::class,
            // Bạn không cần alias 'auth:sanctum' ở đây nữa,
            // vì nó đã được Sanctum service provider tự động đăng ký.
            // Nếu bạn có alias 'auth:sanctum' nào đó, hãy xóa nó đi.
        ]);

        // Đăng ký CorsMiddleware của bạn để chạy ĐẦU TIÊN cho MỌI request
        // Điều này đảm bảo các header CORS được thêm vào trước khi request được xử lý sâu hơn.
        // Dùng `prepend` thay vì `append` để đảm bảo nó chạy sớm.
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        // Hoặc nếu bạn chỉ muốn nó chạy trên các route API, bạn có thể thêm nó vào nhóm 'api'
        // $middleware->api([
        //     \App\Http\Middleware\CorsMiddleware::class,
        // ]);
        // Tuy nhiên, việc prepend toàn cục thường dễ quản lý hơn với CORS.

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();