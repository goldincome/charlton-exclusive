<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0); // pence
            $table->string('pricing_unit')->default('flat'); // hour, day, flat
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->nullableMorphs('source'); // bridge: Hall, Event, DiningTable, MenuItem, etc.
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'section_id')) {
                $table->foreignId('section_id')->nullable()->after('bookingable_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'bookable_item_id')) {
                $table->foreignId('bookable_item_id')->nullable()->after('section_id')->constrained('bookable_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('user_id')->constrained('plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'original_amount')) {
                $table->unsignedBigInteger('original_amount')->nullable()->after('price_locked');
            }
            if (! Schema::hasColumn('bookings', 'final_amount')) {
                $table->unsignedBigInteger('final_amount')->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('bookings', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('reference');
            }
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bookable_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'bookable_item_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('original_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('final_amount')->default(0);
            $table->string('discount_type')->nullable();
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->text('notes')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bookable_item_id')->constrained();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('unit_price')->default(0); // pence snapshot
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('original_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('final_amount')->default(0);
            $table->timestamps();
        });

        Schema::create('section_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('usable'); // booking, order, etc.
            $table->unsignedBigInteger('original_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('final_amount')->default(0);
            $table->string('discount_type')->nullable();
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_usages');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['stripe_session_id', 'final_amount', 'original_amount'] as $col) {
                if (Schema::hasColumn('bookings', $col)) $table->dropColumn($col);
            }
            if (Schema::hasColumn('bookings', 'plan_id')) $table->dropConstrainedForeignId('plan_id');
            if (Schema::hasColumn('bookings', 'bookable_item_id')) $table->dropConstrainedForeignId('bookable_item_id');
            if (Schema::hasColumn('bookings', 'section_id')) $table->dropConstrainedForeignId('section_id');
        });
        Schema::dropIfExists('bookable_items');
    }
};
