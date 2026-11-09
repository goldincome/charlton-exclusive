<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("events", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug")->unique();
            $table->text("description")->nullable();
            $table->dateTime("starts_at");
            $table->dateTime("ends_at");
            $table->integer("capacity");
            $table->integer("tickets_per_booking")->default(1);
            $table->unsignedBigInteger("price_per_ticket");
            $table->string("member_discount_type")->default("percent");
            $table->decimal("member_discount_value", 10, 2)->default(0);
            $table->boolean("active")->default(true);
            $table->timestamp("cancelled_at")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("events");
    }
};