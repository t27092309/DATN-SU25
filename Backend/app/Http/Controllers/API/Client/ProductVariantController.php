<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProductVariantResource;

class ProductVariantController extends Controller
{
    public function show($id)
    {
        try {
            $variant = ProductVariant::with([
                'product' => function ($query) {
                    $query->select('id', 'name', 'image'); // image thay vì thumbnail_url
                },
                'attributeValues.attribute',
            ])->find($id);

            if (!$variant) {
                return response()->json([
                    'message' => 'Biến thể sản phẩm không tìm thấy.'
                ], 404);
            }

            // Trả về bằng ProductVariantResource
            return response()->json([
                'message' => 'Biến thể sản phẩm được tìm thấy thành công.',
                'data' => new ProductVariantResource($variant) // <-- Sử dụng resource
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết biến thể sản phẩm: ' . $e->getMessage(), [
                'variant_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi tải chi tiết biến thể sản phẩm. Vui lòng thử lại.',
                'error' => 'Internal Server Error',
            ], 500);
        }
    }
}