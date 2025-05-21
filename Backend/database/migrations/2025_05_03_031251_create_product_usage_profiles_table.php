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
        Schema::create('product_usage_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('spring_percent')->default(0);
            $table->integer('summer_percent')->default(0);
            $table->integer('autumn_percent')->default(0);
            $table->integer('winter_percent')->default(0);
            $table->integer('suitable_day')->default(0);
            $table->integer('suitable_night')->default(0);
            $table->decimal('longevity_hours', 3, 1);
            $table->string('sillage_range_m');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_usage_profiles');
    }
};
