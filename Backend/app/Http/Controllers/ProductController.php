<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Lấy toàn bộ sản phẩm từ bảng product
        $products = Product::all();

        // Trả về JSON cho Vue
        return response()->json($products);
    }
}
