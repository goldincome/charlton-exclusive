<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("food_orders", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("booking_id")->nullable()->constrained()->nullOnDelete();
            $table->string("type")->default("personal"); // personal, party
            $table->string("status")->default("requested");
            $table->dateTime("requested_for");
            $table->text("dietary_notes")->nullable();
            $table->string("location")->nullable();
            $table->string("contact_phone")->nullable();
            $table->unsignedBigInteger("price_locked")->nullable();
            $table->timestamps();
        });
        Schema::create("food_order_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("food_order_id")->constrained()->cascadeOnDelete();
            $table->foreignId("menu_item_id")->constrained();
            $table->integer("quantity")->default(1);
            $table->unsignedBigInteger("unit_price");
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("food_order_items");
        Schema::dropIfExists("food_orders");
    }
};