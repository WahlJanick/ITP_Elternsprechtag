<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->index(
                ['parent_day_id', 'teacher_id', 'is_reserved', 'starts_at'],
                'timeslots_parent_teacher_status_start_idx'
            );
            $table->index(
                ['student_id', 'is_reserved', 'starts_at', 'ends_at'],
                'timeslots_student_status_range_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('timeslots', function (Blueprint $table) {
            $table->dropIndex('timeslots_parent_teacher_status_start_idx');
            $table->dropIndex('timeslots_student_status_range_idx');
        });
    }
};
