<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('room_rates', 'member_discount_type')) {
            Schema::table('room_rates', function (Blueprint $table) {
                $table->dropColumn(['member_discount_type', 'member_discount_value']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('room_rates', function (Blueprint $table) {
            $table->string('member_discount_type')->default('percent');
            $table->unsignedInteger('member_discount_value')->default(20);
        });
    }
};
