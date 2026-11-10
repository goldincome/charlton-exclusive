<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'membership_category_id')) {
            Schema::table('applications', function (Blueprint $table) {
                try {
                    $table->dropForeign(['membership_category_id']);
                } catch (Throwable) {
                }
            });
        }
        if (Schema::hasTable('memberships') && Schema::hasColumn('memberships', 'membership_category_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                try {
                    $table->dropForeign(['membership_category_id']);
                } catch (Throwable) {
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
