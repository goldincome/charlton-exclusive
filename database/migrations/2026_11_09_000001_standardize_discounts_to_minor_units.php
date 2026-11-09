<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize config member discount types: flat -> fixed. Values: percent kept as points;
        // fixed/flat values were previously treated as major units (*100 in code) so convert to pence.
        Schema::table('room_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('member_discount_value_int')->default(0)->after('member_discount_value');
        });
        DB::table('room_rates')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $type = in_array($row->member_discount_type, ['flat', 'fixed'], true) ? 'fixed' : 'percent';
                $raw = (float) $row->member_discount_value;
                $value = $type === 'fixed' ? (int) round($raw * 100) : (int) round($raw);
                DB::table('room_rates')->where('id', $row->id)->update([
                    'member_discount_type' => $type,
                    'member_discount_value_int' => $value,
                ]);
            }
        });
        Schema::table('room_rates', function (Blueprint $table) {
            $table->dropColumn('member_discount_value');
        });
        Schema::table('room_rates', function (Blueprint $table) {
            $table->renameColumn('member_discount_value_int', 'member_discount_value');
        });

        Schema::table('table_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('member_discount_value_int')->default(0)->after('member_discount_value');
        });
        DB::table('table_categories')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $type = in_array($row->member_discount_type, ['flat', 'fixed'], true) ? 'fixed' : 'percent';
                $raw = (float) $row->member_discount_value;
                $value = $type === 'fixed' ? (int) round($raw * 100) : (int) round($raw);
                DB::table('table_categories')->where('id', $row->id)->update([
                    'member_discount_type' => $type,
                    'member_discount_value_int' => $value,
                ]);
            }
        });
        Schema::table('table_categories', function (Blueprint $table) {
            $table->dropColumn('member_discount_value');
        });
        Schema::table('table_categories', function (Blueprint $table) {
            $table->renameColumn('member_discount_value_int', 'member_discount_value');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('member_discount_value_int')->default(0)->after('member_discount_value');
        });
        DB::table('events')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $type = in_array($row->member_discount_type, ['flat', 'fixed'], true) ? 'fixed' : 'percent';
                $raw = (float) $row->member_discount_value;
                $value = $type === 'fixed' ? (int) round($raw * 100) : (int) round($raw);
                DB::table('events')->where('id', $row->id)->update([
                    'member_discount_type' => $type,
                    'member_discount_value_int' => $value,
                ]);
            }
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('member_discount_value');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('member_discount_value_int', 'member_discount_value');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_value')->default(0)->after('discount_applied');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('discount_value');
        });
        DB::table('bookings')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $type = $row->discount_type;
                $type = in_array($type, ['flat', 'fixed'], true) ? 'fixed' : ($type ? 'percent' : null);
                $raw = (float) ($row->discount_applied ?? 0);
                // Legacy bookings stored discount_applied as a decimal; treat as percent points when percent.
                // For fixed we cannot reliably recover pence from old semantics; preserve best-effort.
                $value = $type === 'percent' ? (int) round($raw) : 0;
                DB::table('bookings')->where('id', $row->id)->update([
                    'discount_type' => $type,
                    'discount_value' => $value,
                    'discount_amount' => 0,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['discount_value', 'discount_amount']);
        });

        foreach (['events', 'table_categories', 'room_rates'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('member_discount_value_dec', 10, 2)->default(0)->after('member_discount_value');
            });
            DB::table($tableName)->orderBy('id')->chunkById(100, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    $type = $row->member_discount_type;
                    $val = (int) $row->member_discount_value;
                    $raw = $type === 'fixed' ? $val / 100 : $val;
                    DB::table($tableName)->where('id', $row->id)->update([
                        'member_discount_type' => $type === 'fixed' ? 'flat' : $type,
                        'member_discount_value_dec' => $raw,
                    ]);
                }
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('member_discount_value');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('member_discount_value_dec', 'member_discount_value');
            });
        }
    }
};
