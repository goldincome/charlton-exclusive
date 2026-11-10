<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('meta_alt')->nullable();
            $table->integer('capacity_seated')->default(0);
            $table->string('area')->nullable();
        });
        Schema::table('events', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('venue')->nullable();
            $table->boolean('members_only')->default(false);
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('icon')->nullable();
        });
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('reference')->unique()->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
        Schema::table('food_orders', function (Blueprint $table) {
            $table->string('reference')->unique()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference')->unique()->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('refusal_basis')->nullable();
            $table->boolean('is_reapplication')->default(false);
        });
        Schema::table('membership_categories', function (Blueprint $table) {
            $table->integer('nominee_limit')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('membership_categories', function (Blueprint $table) {
            $table->dropColumn(['nominee_limit', 'seo_title', 'seo_description']);
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['reference', 'expires_at', 'refusal_basis', 'is_reapplication']);
        });
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn(['reference', 'approved_at', 'expires_at']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reference', 'cancelled_at', 'cancellation_reason', 'rejected_at', 'rejection_reason', 'expires_at']);
        });
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'og_image']);
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'og_image', 'icon']);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'og_image', 'venue', 'members_only']);
        });
        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'og_image', 'meta_alt', 'capacity_seated', 'area']);
        });
    }
};
