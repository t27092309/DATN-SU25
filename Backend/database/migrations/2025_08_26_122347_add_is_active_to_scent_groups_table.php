<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('scent_groups', function (Blueprint $table) {
            $table->boolean('is_active')
                ->default(true)
                ->after('color_code'); // thêm sau cột color_code
        });
    }

    public function down(): void
    {
        Schema::table('scent_groups', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
