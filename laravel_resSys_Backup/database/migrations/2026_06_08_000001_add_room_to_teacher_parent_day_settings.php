<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('teacher_parent_day_settings', 'room')) {
            return;
        }

        Schema::table('teacher_parent_day_settings', function (Blueprint $table) {
            $table->string('room')->nullable()->after('timeslot_duration');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('teacher_parent_day_settings', 'room')) {
            return;
        }

        Schema::table('teacher_parent_day_settings', function (Blueprint $table) {
            $table->dropColumn('room');
        });
    }
};
