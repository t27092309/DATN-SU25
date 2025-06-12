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
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Xử lý các request OPTIONS (preflight)
        // Các trình duyệt gửi request OPTIONS trước các request "phức tạp"
        // để kiểm tra xem server có cho phép cross-origin hay không.
        // Server cần trả về status 204 No Content và các header CORS.
        if ($request->isMethod('OPTIONS')) {
            // Trả về một response 204 mới với các header CORS đã được set
            // Điều này đảm bảo rằng các header CORS được gửi đi ngay lập tức
            // mà không cần phải xử lý thêm bất kỳ logic route nào.
            return response('', 204)
                        ->withHeaders($response->headers->all());
        }

        return $response;
    }
}