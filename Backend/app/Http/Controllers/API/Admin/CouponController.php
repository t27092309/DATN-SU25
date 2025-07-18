<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // GET // http://localhost:8000/api/coupons
   public function index()
{
    $coupons = Coupon::orderBy('id', 'desc')->paginate(15);
    return response()->json($coupons, 200);
}



    // POST // http://localhost:8000/api/coupons
    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        $coupon = Coupon::create($data);
        return response()->json([
            'message' => 'Thêm coupon thành công',
            'coupon' => $coupon
        ], 201);
    }

   // GET // http://localhost:8000/api/coupons/{id}
    public function show(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon, 200);
    }


    // PUT // http://localhost:8000/api/coupons/{id}
    public function update(CouponRequest $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $data = $request->validated();
        $coupon->update($data);
        return response()->json([
            'message' => 'Cập nhật coupon thành công',
            'coupon' => $coupon
        ], 200);
    }


    // DELETE // http://localhost:8000/api/coupons/{id}
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return response()->json([
            'message' => 'Xóa coupon thành công',
        ], 200);
    }
}
