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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('leading_url')->nullable();
            $table->string('background_url')->nullable();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->json('inputs')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
