<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User; // Đảm bảo đã import User model

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // Mặc định là 'password'
            'remember_token' => Str::random(10),
            'role' => 'user', // Mặc định role là 'user'
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a staff member.
     */
    public function staff(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'staff',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Tạo các tài khoản người dùng mặc định (Admin, Staff, và User thường).
     *
     * @param string $adminPassword Mật khẩu cho tài khoản admin.
     * @param string $staffPassword Mật khẩu cho tài khoản staff.
     * @param string $defaultUserPassword Mật khẩu mặc định cho các user thường (nếu muốn).
     * @return void
     */

    // --- PHƯƠNG THỨC TĨNH MỚI ĐỂ GÓI GỌN LOGIC TẠO USER ĐẶC BIỆT ---
    public static function createDefaultUsers(
        string $adminPassword = 'password',
        string $staffPassword = 'password',
        string $defaultUserPassword = 'password' // Mật khẩu mặc định cho user thường

    ): void {

        // Tạo tài khoản Admin cụ thể hoặc tìm nếu đã tồn tại
        User::firstOrCreate(
            ['email' => 'admin@florea.com'], // Tiêu chí tìm kiếm
            [
                'name' => 'Super Admin',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );

        // Tạo tài khoản Staff cụ thể hoặc tìm nếu đã tồn tại
        User::firstOrCreate(
            ['email' => 'staff@florea.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make($staffPassword),
                'role' => 'staff',
            ]
        );

        // Tạo một số lượng lớn user thông thường nếu tổng số user dưới 100
        // (để tránh tạo quá nhiều user nếu đã có sẵn)
        if (User::count() < 100) { // Ví dụ: chỉ tạo thêm nếu tổng số user dưới 100
            // Truyền mật khẩu mặc định cho user thường vào factory
            User::factory()->count(20)->create([
                'password' => Hash::make($defaultUserPassword),
            ]);
        }
    }
}
