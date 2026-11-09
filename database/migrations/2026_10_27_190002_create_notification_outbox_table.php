<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("notification_outbox", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("to_email");
            $table->string("subject");
            $table->longText("template")->nullable();
            $table->string("status")->default("queued"); // queued, sent, failed
            $table->text("error")->nullable();
            $table->timestamp("sent_at")->nullable();
            $table->timestamps();
            $table->index("status");
        });
    }
    public function down(): void {
        Schema::dropIfExists("notification_outbox");
    }
};