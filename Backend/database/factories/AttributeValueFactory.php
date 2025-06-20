<?php

namespace Database\Factories;

use App\Models\Attribute; // Đảm bảo import model Attribute
use App\Models\AttributeValue; // Đảm bảo import model AttributeValue
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttributeValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lấy một attribute_id ngẫu nhiên từ bảng 'attributes' đã có
        // Đảm bảo rằng bạn đã seed bảng 'attributes' trước khi seed 'attribute_values'
        $attributeId = Attribute::inRandomOrder()->first()->id ?? null;

        // Nếu bảng attributes chưa có dữ liệu, bạn có thể tạo một attribute mới tại đây
        if (is_null($attributeId)) {
            $attributeId = Attribute::factory()->create()->id;
        }

        // Tạo một giá trị ngẫu nhiên cho attribute value
        $value = $this->faker->unique()->word(); // Hoặc sử dụng sentence(), colorName(), etc., tùy thuộc vào loại giá trị bạn muốn

        return [
            'attribute_id' => $attributeId,
            'value' => $value,
        ];
    }

    /**
     * Định nghĩa một trạng thái cho các giá trị màu sắc.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function color(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => $this->faker->unique()->safeColorName(),
            ];
        });
    }

    /**
     * Định nghĩa một trạng thái cho các giá trị kích thước.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function size(): Factory
    {
        return $this->state(function (array $attributes) {
            $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
            return [
                'value' => $this->faker->unique()->randomElement($sizes),
            ];
        });
    }

    /**
     * Định nghĩa một trạng thái cho các giá trị số (ví dụ: trọng lượng, dung tích).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function numeric(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => $this->faker->unique()->numberBetween(1, 100), // Ví dụ: giá trị từ 1 đến 100
            ];
        });
    }
}