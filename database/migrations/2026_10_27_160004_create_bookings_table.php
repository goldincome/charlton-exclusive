<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bookingable_type'); // Hall, DiningTable, Event
            $table->unsignedBigInteger('bookingable_id');
            $table->string('status')->default('requested');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('guests')->default(1);
            $table->unsignedBigInteger('price_locked')->nullable();
            $table->decimal('discount_applied', 10, 2)->default(0);
            $table->string('discount_type')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->index(['bookingable_type', 'bookingable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
