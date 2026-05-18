<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_parent_day_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('teacher_id');
            $table->foreign('teacher_id')
                ->references('teacher_id')
                ->on('teachers')
                ->onDelete('cascade');
            $table->foreignId('parent_day_id')
                ->constrained('parent_days')
                ->onDelete('cascade');
            $table->unsignedInteger('timeslot_duration')->nullable();
            $table->dateTime('duration_changed_at')->nullable();
            $table->boolean('duration_changed_by_teacher')->default(false);
            $table->timestamps();

            $table->unique(['teacher_id', 'parent_day_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_parent_day_settings');
    }
};
