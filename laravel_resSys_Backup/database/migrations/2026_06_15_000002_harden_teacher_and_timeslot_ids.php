<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeDuplicateTimeslots();

        Schema::table('timeslots', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'parent_day_id', 'starts_at'],
                'timeslots_teacher_parent_start_unique'
            );
        });

        if (DB::getDriverName() === 'pgsql') {
            $this->configurePostgresSequence('teachers', 'teacher_id', 'teachers_teacher_id_seq');
            $this->configurePostgresSequence('timeslots', 'id', 'timeslots_id_seq');
        }
    }

    public function down(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->dropUnique('timeslots_teacher_parent_start_unique');
        });
    }

    private function removeDuplicateTimeslots(): void
    {
        $duplicates = DB::table('timeslots')
            ->select(['teacher_id', 'parent_day_id', 'starts_at'])
            ->selectRaw('COUNT(*) AS duplicate_count')
            ->groupBy(['teacher_id', 'parent_day_id', 'starts_at'])
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $query = DB::table('timeslots')
                ->where('teacher_id', $duplicate->teacher_id)
                ->where('starts_at', $duplicate->starts_at);

            if ($duplicate->parent_day_id === null) {
                $query->whereNull('parent_day_id');
            } else {
                $query->where('parent_day_id', $duplicate->parent_day_id);
            }

            $duplicateIds = $query
                ->orderByDesc('is_reserved')
                ->orderBy('id')
                ->pluck('id')
                ->slice(1)
                ->values()
                ->all();

            if ($duplicateIds !== []) {
                DB::table('timeslots')->whereIn('id', $duplicateIds)->delete();
            }
        }
    }

    private function configurePostgresSequence(string $table, string $column, string $sequence): void
    {
        DB::statement("CREATE SEQUENCE IF NOT EXISTS {$sequence}");
        DB::statement("ALTER SEQUENCE {$sequence} OWNED BY {$table}.{$column}");
        DB::statement(
            "ALTER TABLE {$table} ALTER COLUMN {$column} SET DEFAULT nextval('{$sequence}'::regclass)"
        );
        DB::statement(
            "SELECT setval('{$sequence}', COALESCE((SELECT MAX({$column}) FROM {$table}), 0) + 1, false)"
        );
    }
};
