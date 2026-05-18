<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->foreignId('parent_day_id')
                ->nullable()
                ->after('teacher_id')
                ->constrained('parent_days')
                ->cascadeOnDelete();
        });

        if (! Schema::hasTable('parent_days')) {
            return;
        }

        $timeslots = DB::table('timeslots')
            ->select(['id', 'day', 'starts_at'])
            ->get();

        if ($timeslots->isEmpty()) {
            return;
        }

        $dateMap = [];

        foreach ($timeslots as $timeslot) {
            $dateValue = null;

            if (! empty($timeslot->day)) {
                $dateValue = Carbon::parse($timeslot->day)->toDateString();
            } elseif (! empty($timeslot->starts_at)) {
                $dateValue = Carbon::parse($timeslot->starts_at)->toDateString();
            }

            if (! $dateValue) {
                continue;
            }

            if (! array_key_exists($dateValue, $dateMap)) {
                $parentDayId = DB::table('parent_days')->where('date', $dateValue)->value('id');

                if (! $parentDayId) {
                    $parentDayId = DB::table('parent_days')->insertGetId([
                        'date' => $dateValue,
                        'label' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $dateMap[$dateValue] = $parentDayId;
            }

            if (isset($dateMap[$dateValue])) {
                DB::table('timeslots')
                    ->where('id', $timeslot->id)
                    ->update([
                        'parent_day_id' => $dateMap[$dateValue],
                        'day' => $dateValue,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_day_id');
        });
    }
};
