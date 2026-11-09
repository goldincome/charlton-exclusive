<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("users", function (Blueprint $table) {
            $table->string("phone")->nullable();
            $table->string("address_line_1")->nullable();
            $table->string("address_line_2")->nullable();
            $table->string("city")->nullable();
            $table->string("postcode")->nullable();
            $table->date("date_of_birth")->nullable();
            $table->boolean("committee_member")->default(false);
            $table->string("status")->default("active");
        });
    }
    public function down(): void {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["phone", "address_line_1", "address_line_2", "city", "postcode", "date_of_birth", "committee_member", "status"]);
        });
    }
};