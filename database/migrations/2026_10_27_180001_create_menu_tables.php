<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('image_path')->nullable();
            $table->boolean('discount_exempt')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('menu_item_allergens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('allergen');
            $table->timestamps();
            $table->unique(['menu_item_id', 'allergen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_allergens');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
    }
};
