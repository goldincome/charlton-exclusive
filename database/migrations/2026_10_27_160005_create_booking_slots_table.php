<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type'); // hall, table, event
            $table->unsignedBigInteger('resource_id');
            $table->dateTime('slot_start');
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['resource_type', 'resource_id', 'slot_start'], 'booking_slot_unique');
            $table->index(['resource_type', 'resource_id', 'slot_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
