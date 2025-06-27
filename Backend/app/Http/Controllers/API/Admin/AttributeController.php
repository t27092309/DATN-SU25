<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Http\Resources\AttributeResource; // <-- Đảm bảo import
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributes = Attribute::with('attributeValues')->latest()->paginate(10);
        // Consistently use AttributeResource for collection
        return AttributeResource::collection($attributes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name'],
        ]);

        $attribute = Attribute::create([
            'name' => $validatedData['name'], // Use validated data
            'slug' => Str::slug($validatedData['name']),
        ]);

        // Consistently return the created resource
        return new AttributeResource($attribute); // Laravel automatically sets 201 Created status
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->load('attributeValues');
        // Consistently return the single resource
        return new AttributeResource($attribute);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'], // No need for unique check here if slug is auto-generated
            // If you still want to allow slug to be sent and updated, keep the unique rule:
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes')->ignore($attribute->id)],
        ]);

        // If you are not sending 'slug' from frontend and want to regenerate it
        if (!isset($validatedData['slug']) || empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }

        $attribute->update($validatedData);

        // Consistently return the updated resource
        return new AttributeResource($attribute);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        // Return 204 No Content for successful deletion, as no data is returned
        return response()->noContent();
    }
}