<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        Schema::create('hall_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('hall_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0=Sunday .. 6=Saturday
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
            $table->unique(['hall_id', 'weekday']);
        });
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->string('checkable_type');
            $table->unsignedBigInteger('checkable_id');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at');
            $table->timestamps();
            $table->index(['checkable_type', 'checkable_id']);
        });
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('product_type')->unique(); // hall, table, event, food, membership
            $table->decimal('rate', 5, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->boolean('handled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('vat_rates');
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('hall_hours');
        Schema::dropIfExists('hall_features');
        Schema::dropIfExists('hall_images');
    }
};
