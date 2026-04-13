<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->integer('student_id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('class_name');
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->integer('teacher_id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->json('classes');
        });

        Schema::create('timeslots', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('teacher_id');
            $table->foreign('teacher_id')
                ->references('teacher_id')
                ->on('teachers')
                ->onDelete('cascade');
            $table->integer('student_id')->nullable();
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('room');
            $table->boolean('is_reserved')->default(false);
            $table->date('day');

        });
    }


};
