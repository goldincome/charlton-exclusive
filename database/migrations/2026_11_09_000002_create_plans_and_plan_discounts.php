<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('benefits')->nullable();
            $table->unsignedBigInteger('entrance_fee')->default(0);
            $table->unsignedBigInteger('annual_fee')->default(0);
            $table->unsignedBigInteger('monthly_fee')->nullable();
            $table->unsignedBigInteger('price')->default(0); // primary display/subscription price (pence)
            $table->string('billing_interval')->nullable(); // year, month
            $table->unsignedInteger('min_sections')->default(0);
            $table->unsignedInteger('nominee_limit')->default(2);
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_sync_status')->nullable(); // pending, synced, failed
            $table->text('stripe_sync_error')->nullable();
            $table->timestamp('stripe_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('membership_categories')) {
            $rows = DB::table('membership_categories')->get();
            foreach ($rows as $row) {
                DB::table('plans')->insert([
                    'id' => $row->id,
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'description' => $row->description,
                    'benefits' => $row->benefits,
                    'entrance_fee' => (int) $row->entrance_fee,
                    'annual_fee' => (int) $row->annual_fee,
                    'monthly_fee' => $row->monthly_fee !== null ? (int) $row->monthly_fee : null,
                    'price' => (int) $row->annual_fee,
                    'billing_interval' => 'year',
                    'min_sections' => (int) ($row->min_sections ?? 0),
                    'nominee_limit' => (int) ($row->nominee_limit ?? 2),
                    'is_active' => (bool) $row->active,
                    'seo_title' => $row->seo_title ?? null,
                    'seo_description' => $row->seo_description ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::table('sections', function (Blueprint $table) {
            $table->string('type')->default('slot')->after('slug'); // slot|cart
            $table->string('access_type')->default('members_only')->after('type'); // open|members_only
        });

        Schema::create('plan_section_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('percent'); // percent|fixed (fixed=pence)
            $table->unsignedBigInteger('value')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['plan_id', 'section_id']);
        });

        if (Schema::hasTable('category_section')) {
            $rows = DB::table('category_section')->get();
            foreach ($rows as $row) {
                DB::table('plan_section_discounts')->insertOrIgnore([
                    'plan_id' => $row->membership_category_id,
                    'section_id' => $row->section_id,
                    'type' => 'percent',
                    'value' => 0,
                    'is_active' => true,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable('applications') && ! Schema::hasColumn('applications', 'plan_id')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->foreignId('plan_id')->nullable()->after('membership_category_id')->constrained('plans')->nullOnDelete();
            });
            DB::table('applications')->whereNotNull('membership_category_id')->update(['plan_id' => DB::raw('membership_category_id')]);
        }

        if (Schema::hasTable('memberships') && ! Schema::hasColumn('memberships', 'plan_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->foreignId('plan_id')->nullable()->after('membership_category_id')->constrained('plans')->nullOnDelete();
            });
            DB::table('memberships')->whereNotNull('membership_category_id')->update(['plan_id' => DB::raw('membership_category_id')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('memberships', 'plan_id')) {
            Schema::table('memberships', fn (Blueprint $table) => $table->dropConstrainedForeignId('plan_id'));
        }
        if (Schema::hasColumn('applications', 'plan_id')) {
            Schema::table('applications', fn (Blueprint $table) => $table->dropConstrainedForeignId('plan_id'));
        }
        Schema::dropIfExists('plan_section_discounts');
        if (Schema::hasColumn('sections', 'access_type')) {
            Schema::table('sections', fn (Blueprint $table) => $table->dropColumn(['type', 'access_type']));
        }
        Schema::dropIfExists('plans');
    }
};
