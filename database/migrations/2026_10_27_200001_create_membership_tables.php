<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("category_section", function (Blueprint $table) {
            $table->id();
            $table->foreignId("membership_category_id")->constrained()->cascadeOnDelete();
            $table->foreignId("section_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(["membership_category_id", "section_id"]);
        });
        Schema::create("application_sections", function (Blueprint $table) {
            $table->id();
            $table->foreignId("application_id")->constrained()->cascadeOnDelete();
            $table->foreignId("section_id")->constrained();
            $table->timestamps();
            $table->unique(["application_id", "section_id"]);
        });
        Schema::create("memberships", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("membership_category_id")->constrained();
            $table->string("billing_frequency")->default("annual"); // annual, monthly
            $table->string("status")->default("pending"); // pending, active, suspended, terminated, cancelled
            $table->date("starts_at")->nullable();
            $table->date("renews_at")->nullable();
            $table->date("ends_at")->nullable();
            $table->string("stripe_subscription_id")->nullable();
            $table->timestamps();
        });
        Schema::create("membership_persons", function (Blueprint $table) {
            $table->id();
            $table->foreignId("membership_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("role")->default("primary"); // primary, partner, nominee
            $table->string("name")->nullable();
            $table->string("email")->nullable();
            $table->timestamps();
        });
        Schema::create("interviews", function (Blueprint $table) {
            $table->id();
            $table->foreignId("application_id")->constrained()->cascadeOnDelete();
            $table->dateTime("scheduled_at");
            $table->string("location")->nullable();
            $table->text("notes")->nullable();
            $table->string("recommendation")->nullable(); // eligible, ineligible
            $table->timestamps();
        });
        Schema::create("interview_attendees", function (Blueprint $table) {
            $table->id();
            $table->foreignId("interview_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained();
            $table->timestamps();
        });
        Schema::create("decision_letters", function (Blueprint $table) {
            $table->id();
            $table->foreignId("application_id")->constrained()->cascadeOnDelete();
            $table->string("path")->nullable();
            $table->text("reason")->nullable();
            $table->timestamp("sent_at")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("decision_letters");
        Schema::dropIfExists("interview_attendees");
        Schema::dropIfExists("interviews");
        Schema::dropIfExists("membership_persons");
        Schema::dropIfExists("memberships");
        Schema::dropIfExists("application_sections");
        Schema::dropIfExists("category_section");
    }
};