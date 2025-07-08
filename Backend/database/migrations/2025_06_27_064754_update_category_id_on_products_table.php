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
    Schema::table('products', function (Blueprint $table) {
        // Bỏ foreign key cũ trước (nếu đã có)
        $table->dropForeign(['category_id']);

        // Cập nhật lại column cho phép null và set null khi danh mục bị xóa
        $table->unsignedBigInteger('category_id')->nullable()->change();
        $table->foreign('category_id')
              ->references('id')
              ->on('categories')
              ->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        // Quay về như cũ nếu rollback
        $table->dropForeign(['category_id']);
        $table->unsignedBigInteger('category_id')->nullable(false)->change();
        $table->foreign('category_id')
              ->references('id')
              ->on('categories');
    });
}
};
