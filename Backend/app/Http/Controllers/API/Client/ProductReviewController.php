<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index($slug)
    {
        // 1. Tìm sản phẩm dựa trên slug, nếu không tìm thấy thì báo lỗi
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }

        // 2. Lấy tất cả đánh giá (reviews) của sản phẩm đó
        // Bạn có thể eager load thêm thông tin người dùng nếu cần
        $reviews = $product->reviews()->with('user:id,name,avatar')->latest()->paginate(10);

        // 3. Trả về danh sách reviews
        return response()->json([
            'message' => 'Lấy danh sách đánh giá thành công.',
            'reviews' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ]
        ]);
    }
}