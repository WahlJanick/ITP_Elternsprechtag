<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->unsignedInteger('timeslot_duration')->nullable();
            $table->dateTime('duration_changed_at')->nullable();
            $table->boolean('duration_changed_by_teacher')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['timeslot_duration', 'duration_changed_at', 'duration_changed_by_teacher']);
        });
    }
};
