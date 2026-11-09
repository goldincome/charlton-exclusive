<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('status');
            $table->string('id_type')->nullable()->after('profession');
            $table->string('cv_path')->nullable()->after('id_type');
            $table->string('id_path')->nullable()->after('cv_path');
            $table->string('application_method')->nullable()->after('id_path');
            $table->timestamp('documents_verified_at')->nullable()->after('application_method');
            $table->foreignId('documents_verified_by')->nullable()->after('documents_verified_at')->constrained('users')->nullOnDelete();
            $table->string('billing_frequency')->nullable()->after('documents_verified_by');
        });

        // The plan is chosen by an administrator at approval time, so it may be empty while the
        // application is pending. The legacy membership_category_id column mirrors plan_id.
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_category_id')->nullable()->change();
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_monthly_price_id')->nullable()->after('stripe_price_id');
            $table->string('stripe_annual_price_id')->nullable()->after('stripe_monthly_price_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stripe_invoice_id')->nullable()->unique()->after('total');
            $table->text('stripe_hosted_invoice_url')->nullable()->after('stripe_invoice_id');
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('ends_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
        });

        // There is no entrance fee in the current lifecycle: one fee, monthly or yearly.
        DB::table('plans')->update(['entrance_fee' => 0]);
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['stripe_invoice_id']);
            $table->dropColumn(['stripe_invoice_id', 'stripe_hosted_invoice_url']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['stripe_monthly_price_id', 'stripe_annual_price_id']);
        });

        $fallback = DB::table('plans')->orderBy('entrance_fee')->value('id');
        DB::table('applications')->whereNull('membership_category_id')->update([
            'membership_category_id' => $fallback,
        ]);

        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_category_id')->nullable(false)->change();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('documents_verified_by');
            $table->dropColumn([
                'profession', 'id_type', 'cv_path', 'id_path', 'application_method',
                'documents_verified_at', 'billing_frequency',
            ]);
        });
    }
};
