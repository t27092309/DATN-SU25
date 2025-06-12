<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    // GET // http://localhost:8000/api/products

    public function index()
    {
        return Product::with(['images', 'variants'])->get();
    }

   // POST // http://localhost:8000/api/products


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

     // GET // http://localhost:8000/api/products/{id}


    public function show(string $id)
    {
        return Product::with(['images', 'variants'])->findOrFail($id);
    }

     // PUT // http://localhost:8000/api/products/{id}

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:products,slug,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'gender' => 'in:male,female,unisex',
            'price' => 'nullable|numeric',
            'category_id' => 'exists:categories,id',
            'brand_id' => 'exists:brands,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        return response()->json($product);
    }

    // DELETE // http://localhost:8000/api/products/{id}

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $product->id
        ], 200);
    }

    }

