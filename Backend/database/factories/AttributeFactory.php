<?php

namespace Database\Factories;

use App\Models\Attribute; // Make sure to import your Attribute model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // For generating slugs

class AttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word(); // Generate a unique random word for the name

        return [
            'name' => $name,
            'slug' => Str::slug($name), // Generate a slug from the name
        ];
    }
}