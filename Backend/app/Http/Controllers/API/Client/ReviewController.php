<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        Log::info('Review request received.', ['request_data' => $request->all()]); // Log request đầu vào

        // 1. Validate the incoming request data
        $request->validate([
            'order_item_id' => 'required|integer|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        Log::info('User ID and Order Item ID.', ['user_id' => $userId, 'order_item_id' => $request->order_item_id]); // Log User ID

        // 2. Find OrderItem and eager load the necessary relationships
        $orderItem = OrderItem::with(['order', 'productVariant.product'])
            ->where('id', $request->order_item_id)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'delivered');
            })
            ->doesntHave('review')
            ->first();

        if (!$orderItem) {
            Log::warning('OrderItem not found or not eligible for review.', [
                'order_item_id' => $request->order_item_id,
                'user_id' => $userId
            ]); // Log lỗi khi không tìm thấy OrderItem
            return response()->json([
                'message' => 'Bạn không thể đánh giá sản phẩm này. Có thể đơn hàng chưa được giao, bạn đã đánh giá rồi, hoặc sản phẩm không tồn tại.',
            ], 403);
        }

        Log::info('OrderItem found.', ['order_item_id' => $orderItem->id, 'product_variant_id' => $orderItem->product_variant_id]); // Log OrderItem đã tìm thấy

        // Check if the product variant and product exist before creating the review
        if (!$orderItem->productVariant) {
            Log::error('ProductVariant not found for OrderItem.', ['order_item_id' => $orderItem->id]); // Log lỗi khi không tìm thấy ProductVariant
            return response()->json([
                'message' => 'Sản phẩm liên kết với đơn hàng này không còn tồn tại.',
            ], 404);
        }

        if (!$orderItem->productVariant->product) {
            Log::error('Product not found for ProductVariant.', ['product_variant_id' => $orderItem->productVariant->id]); // Log lỗi khi không tìm thấy Product
            return response()->json([
                'message' => 'Sản phẩm liên kết với đơn hàng này không còn tồn tại.',
            ], 404);
        }

        // Lấy product_id để log trước khi tạo review
        $productId = $orderItem->productVariant->product->id;
        Log::info('Final data before creating review.', [
            'user_id' => $userId,
            'product_id' => $productId,
            'order_item_id' => $orderItem->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]); // Log dữ liệu cuối cùng

        // 3. Create the new review
        Review::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_item_id' => $orderItem->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);
        
        // 4. Update the has_review column in order_items table
        $orderItem->has_review = true;
        $orderItem->save();

        Log::info('Review created successfully.', ['order_item_id' => $orderItem->id]); // Log thành công

        return response()->json([
            'message' => 'Đánh giá của bạn đã được gửi thành công!',
        ], 201);
    }

    /**
     * Kiểm tra xem các OrderItem đã được đánh giá hay chưa.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_item_ids' => 'required|array',
            'order_item_ids.*' => 'integer|exists:order_items,id',
        ]);

        $userId = $request->user()->id;

        $statuses = OrderItem::whereIn('id', $request->input('order_item_ids'))
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->select('id') // Select only the ID for efficiency
            ->withCount('review') // Eager load the count of reviews
            ->get()
            ->map(function ($item) {
                return [
                    'order_item_id' => $item->id,
                    'has_review' => $item->review_count > 0,
                ];
            })
            ->toArray();

        return response()->json([
            'message' => 'Kiểm tra trạng thái đánh giá thành công.',
            'statuses' => $statuses,
        ]);
    }
}
