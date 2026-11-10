<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (! Schema::hasColumn('users', 'address_line_1')) {
                $table->string('address_line_1')->nullable();
            }
            if (! Schema::hasColumn('users', 'address_line_2')) {
                $table->string('address_line_2')->nullable();
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode')->nullable();
            }
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (! Schema::hasColumn('users', 'committee_member')) {
                $table->boolean('committee_member')->default(false);
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address_line_1', 'address_line_2', 'city', 'postcode', 'date_of_birth', 'committee_member', 'status']);
        });
    }
};
