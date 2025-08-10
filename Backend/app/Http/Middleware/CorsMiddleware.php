<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy giá trị URL của frontend từ biến môi trường FRONTEND_URL trong file .env
        // Nếu không tìm thấy, mặc định là 'http://localhost:5173' cho môi trường dev.
        $allowedOrigin = env('FRONTEND_URL', 'http://localhost:5173');

        // Tạo response bằng cách xử lý request tiếp theo trong chuỗi middleware
        $response = $next($request);

        // Thiết lập các header CORS cần thiết
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        // >>> THAY ĐỔI TẠI ĐÂY: THÊM 'PATCH' VÀO DANH SÁCH <<<
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400'); // Nên thêm Max-Age để cache preflight

        // Xử lý các request OPTIONS (preflight)
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                        ->withHeaders($response->headers->all());
        }

        return $response;
    }
}