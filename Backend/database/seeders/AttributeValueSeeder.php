<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo bạn đã chạy AttributeSeeder trước hoặc tạo Attributes tại đây nếu cần
        // Nếu bạn đang chạy db:seed tổng thể, AttributeSeeder sẽ chạy trước
        if (Attribute::count() === 0) {
            Attribute::factory()->count(5)->create(); // Tạo một số thuộc tính nếu chưa có
        }

        // Tạo 30 giá trị thuộc tính ngẫu nhiên
        AttributeValue::factory()->count(30)->create();

        // Ví dụ với các states:
        // Lấy hoặc tạo một thuộc tính "Color"
        $colorAttribute = Attribute::firstOrCreate(
            ['slug' => 'color'],
            ['name' => 'Color']
        );
        AttributeValue::factory()->count(5)->color()->create(['attribute_id' => $colorAttribute->id]);

        // Lấy hoặc tạo một thuộc tính "Size"
        $sizeAttribute = Attribute::firstOrCreate(
            ['slug' => 'size'],
            ['name' => 'Size']
        );
        AttributeValue::factory()->count(6)->size()->create(['attribute_id' => $sizeAttribute->id]);
    }
}