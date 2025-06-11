<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role; // Đảm bảo bạn đã import model Role của mình

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Định nghĩa các giá trị enum hợp lệ cho cột 'name'
        $roleNames = ['admin', 'user', 'staff'];

        return [
            // Chọn ngẫu nhiên một trong các giá trị enum làm tên role
            'name' => $this->faker->randomElement($roleNames),
            // 'timestamps' được Laravel tự động xử lý khi tạo bản ghi.
            // Nếu bạn có thêm các cột khác trong bảng 'roles', hãy định nghĩa chúng ở đây.
        ];
    }

    /**
     * State to create an 'admin' role.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'admin',
            ];
        });
    }

    /**
     * State to create a 'user' role.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function user(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'user',
            ];
        });
    }

    /**
     * State to create a 'staff' role.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function staff(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'staff',
            ];
        });
    }
}