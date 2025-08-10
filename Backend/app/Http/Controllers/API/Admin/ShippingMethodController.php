<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Lấy tất cả các phương thức vận chuyển
        $shippingMethods = ShippingMethod::all();
        return response()->json($shippingMethods);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'is_active' => 'boolean', // Thêm validation cho is_active
                'delivery_time_unit' => 'nullable|in:hours,days',
                'delivery_time_min' => 'nullable|integer|min:0',
                'delivery_time_max' => 'nullable|integer|min:0|gte:delivery_time_min',
            ]);

            $shippingMethod = ShippingMethod::create($validatedData);

            return response()->json($shippingMethod, 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create shipping method.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShippingMethod  $shippingMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShippingMethod $shippingMethod)
    {
        return response()->json($shippingMethod);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShippingMethod  $shippingMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'is_active' => 'sometimes|boolean', // Thêm validation cho is_active
                'delivery_time_unit' => 'nullable|in:hours,days',
                'delivery_time_min' => 'nullable|integer|min:0',
                'delivery_time_max' => 'nullable|integer|min:0|gte:delivery_time_min',
            ]);

            $shippingMethod->update($validatedData);

            return response()->json($shippingMethod);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update shipping method.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShippingMethod  $shippingMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        try {
            $shippingMethod->delete();
            return response()->json(null, 204); // 204 No Content
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete shipping method.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}