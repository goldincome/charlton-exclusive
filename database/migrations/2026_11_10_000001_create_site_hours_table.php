<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday')->unique(); // 0=Sunday .. 6=Saturday
            $table->time('opens_at')->default('08:00:00');
            $table->time('closes_at')->default('23:00:00');
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        $weekdays = [0, 1, 2, 3, 4, 5, 6];
        foreach ($weekdays as $weekday) {
            DB::table('site_hours')->insert([
                'weekday' => $weekday,
                'opens_at' => '08:00:00',
                'closes_at' => '23:00:00',
                'is_open' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_hours');
    }
};
