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
            $table->uuid('uuid')->unique();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->integer('price');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('image_url')->nullable();
            $table->integer('stock')->default(0); // Quantidade em estoque
            $table->string('sku', 100)->unique()->nullable(); // Stock Keeping Unit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('uuid');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('slug');
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
