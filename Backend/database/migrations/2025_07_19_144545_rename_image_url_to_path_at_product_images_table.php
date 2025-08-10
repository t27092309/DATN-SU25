<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Đổi tên cột 'image_url' thành 'path'
            // Đảm bảo không có dữ liệu nào bị mất nếu bạn đã có ảnh trong bảng này.
            $table->renameColumn('image_url', 'path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Hoàn tác: Đổi tên cột 'path' về lại 'image_url'
            $table->renameColumn('path', 'image_url');
        });
    }
};