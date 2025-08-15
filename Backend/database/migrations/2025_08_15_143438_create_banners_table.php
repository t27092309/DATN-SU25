<?php

// database/migrations/..._create_banners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Tiêu đề banner
            $table->string('image_url'); // Đường dẫn đến hình ảnh banner
            $table->string('link_url')->nullable(); // Đường dẫn khi người dùng click vào
            $table->text('description')->nullable(); // Mô tả ngắn
            $table->boolean('is_active')->default(true); // Trạng thái kích hoạt banner
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
