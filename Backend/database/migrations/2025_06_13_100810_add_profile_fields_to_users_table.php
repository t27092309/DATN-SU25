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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('email');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone_number');
            $table->date('birthday')->nullable()->after('gender');
            $table->string('avatar')->nullable()->after('birthday'); // URL hoặc tên file ảnh
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'gender', 'birthday', 'avatar']);
        });
    }
};
