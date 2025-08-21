<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * Lấy danh sách tất cả banner, có phân trang.
     */
    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->paginate(10);
        return response()->json($banners);
    }
}
