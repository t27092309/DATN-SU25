<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariantAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException; // Use this specific exception

class ProductVariantAttributeValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductVariantAttributeValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Ensure ProductVariant exists
        $productVariant = ProductVariant::inRandomOrder()->first();
        if (!$productVariant) {
            $product = Product::factory()->create();
            $productVariant = ProductVariant::factory()->create(['product_id' => $product->id]);
        }
        $productVariantId = $productVariant->id;

        // 2. Select or create an Attribute
        // Get all existing Attribute IDs
        $allAttributes = Attribute::all(); // Get all Attribute models

        // Get Attribute IDs already assigned to this ProductVariant
        $assignedAttributeIds = ProductVariantAttributeValue::where('product_variant_id', $productVariantId)
                                                            ->pluck('attribute_id')
                                                            ->toArray();

        // Find Attribute models that are NOT yet assigned to this ProductVariant
        $possibleAttributesToAssign = $allAttributes->reject(function ($attr) use ($assignedAttributeIds) {
            return in_array($attr->id, $assignedAttributeIds);
        });

        $attribute = null;
        if ($possibleAttributesToAssign->isNotEmpty()) {
            // Prioritize picking an unassigned attribute from the existing pool
            $attribute = $possibleAttributesToAssign->random();
        } else {
            // If all existing attributes are assigned to this variant, or no attributes exist,
            // then create a new, truly unique attribute.
            $attribute = $this->createNewAttribute();
        }
        $attributeId = $attribute->id;

        // 3. Find or create an AttributeValue for the chosen Attribute
        $attributeValue = $attribute->values()->inRandomOrder()->first();
        if (!$attributeValue) {
            $attributeValue = $this->createNewAttributeValueFor($attribute);
        }
        $attributeValueId = $attributeValue->id;

        return [
            'product_variant_id' => $productVariantId,
            'attribute_id' => $attributeId,
            'attribute_value_id' => $attributeValueId,
        ];
    }

    /**
     * Helper to create a new Attribute.
     */
    protected function createNewAttribute(): Attribute
    {
        $commonAttributeData = [
            ['name' => 'Color', 'slug' => 'color'],
            ['name' => 'Size', 'slug' => 'size'],
            ['name' => 'Material', 'slug' => 'material'],
            ['name' => 'Weight', 'slug' => 'weight'],
            ['name' => 'Capacity', 'slug' => 'capacity'],
            ['name' => 'Style', 'slug' => 'style'],
            ['name' => 'Pattern', 'slug' => 'pattern'],
            ['name' => 'Brand', 'slug' => 'brand'],
            ['name' => 'Processor', 'slug' => 'processor'],
        ];

        $existingSlugs = Attribute::pluck('slug')->toArray();
        $availableForCreation = collect($commonAttributeData)->reject(function($data) use ($existingSlugs) {
            return in_array($data['slug'], $existingSlugs);
        })->toArray();

        if (!empty($availableForCreation)) {
            $attributeData = $this->faker->randomElement($availableForCreation);
            return Attribute::firstOrCreate(['slug' => $attributeData['slug']], $attributeData);
        }

        // Fallback: create a truly unique attribute if common ones are exhausted
        return Attribute::factory()->create([
            'name' => $this->faker->unique()->words(2, true) . ' ' . $this->faker->randomNumber(3),
            'slug' => $this->faker->unique()->slug(),
        ]);
    }

    /**
     * Helper to create a new AttributeValue for a given Attribute.
     */
    protected function createNewAttributeValueFor(Attribute $attribute): AttributeValue
    {
        return match ($attribute->slug) {
            'color' => AttributeValue::factory()->color()->create(['attribute_id' => $attribute->id]),
            'size' => AttributeValue::factory()->size()->create(['attribute_id' => $attribute->id]),
            'material' => AttributeValue::factory()->material()->create(['attribute_id' => $attribute->id]),
            'weight', 'capacity' => AttributeValue::factory()->numeric()->create(['attribute_id' => $attribute->id]),
            'brand' => AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => $this->faker->company()]),
            'processor' => AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => $this->faker->randomElement(['Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5', 'AMD Ryzen 7'])]),
            default => AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => $this->faker->word()]),
        };
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        // No need for heavy unique logic here, definition() handles it.
        return $this;
    }
}