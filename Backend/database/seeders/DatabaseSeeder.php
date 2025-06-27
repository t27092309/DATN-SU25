<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            //Bảng độc lập trước
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ScentGroupSeeder::class,


            ShippingMethodSeeder::class,
            WarehouseSeeder::class,
            CouponSeeder::class,

            // Bảng phụ thuộc bảng trên
            RoleUserSeeder::class,
            UserAddressSeeder::class,


            ProductSeeder::class,
            // ProductImageSeeder::class,

            AttributeSeeder::class,
            AttributeValueSeeder::class,
            // ProductVariantAttributeValueSeeder::class,
            ProductVariantSeeder::class,


            ProductUsageProfileSeeder::class,
            ProductScentProfileSeeder::class,

            ReviewSeeder::class,
            WishlistSeeder::class,

            CartSeeder::class,
            CartItemSeeder::class,

            OrderSeeder::class,


            OrderItemSeeder::class,
            PaymentSeeder::class,

            ShippingTrackingSeeder::class,
            OrderReturnSeeder::class,

            WarehouseStockSeeder::class,
            InventoryLogSeeder::class,
        ]);
    }
}
