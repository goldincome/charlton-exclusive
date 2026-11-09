<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("applications", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("membership_category_id")->constrained();
            $table->string("status")->default("draft");
            $table->boolean("application_fee_active")->default(false);
            $table->unsignedBigInteger("application_fee")->default(0);
            $table->timestamp("submitted_at")->nullable();
            $table->timestamp("approved_at")->nullable();
            $table->timestamp("rejected_at")->nullable();
            $table->text("rejection_reason")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("applications");
    }
};