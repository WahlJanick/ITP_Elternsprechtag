<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS timeslots_teacher_reserved_day_index ON timeslots (teacher_id, is_reserved, day)');
        DB::statement('CREATE INDEX IF NOT EXISTS timeslots_student_reserved_day_index ON timeslots (student_id, is_reserved, day)');
        DB::statement('CREATE INDEX IF NOT EXISTS timeslots_reserved_day_index ON timeslots (is_reserved, day)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS timeslots_reserved_day_index');
        DB::statement('DROP INDEX IF EXISTS timeslots_student_reserved_day_index');
        DB::statement('DROP INDEX IF EXISTS timeslots_teacher_reserved_day_index');
    }
};
