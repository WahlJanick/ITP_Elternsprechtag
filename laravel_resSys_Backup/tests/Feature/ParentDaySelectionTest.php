<?php

use App\Models\ParentDay;
use App\Models\Teacher;
use App\Models\Timeslot;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (DB::getDriverName() !== 'sqlite') {
        $this->markTestSkipped('This regression test requires the isolated SQLite test database.');
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->boolean('is_teacher')->default(false);
        $table->boolean('is_admin')->default(false);
        $table->integer('teacher_id')->nullable();
        $table->timestamps();
    });

    Schema::create('teachers', function (Blueprint $table) {
        $table->integer('teacher_id')->primary();
        $table->string('first_name');
        $table->string('last_name');
        $table->json('classes');
    });

    Schema::create('parent_days', function (Blueprint $table) {
        $table->id();
        $table->date('date')->unique();
        $table->string('label')->nullable();
        $table->timestamps();
    });

    Schema::create('timeslots', function (Blueprint $table) {
        $table->integer('id')->primary();
        $table->integer('teacher_id');
        $table->foreignId('parent_day_id')->nullable();
        $table->integer('student_id')->nullable();
        $table->dateTime('starts_at');
        $table->dateTime('ends_at');
        $table->string('room');
        $table->boolean('is_reserved')->default(false);
    });
});

test('a student stays on the current page after switching parent days', function () {
    $user = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => false,
    ]);

    $teacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);

    $parentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);

    Timeslot::create([
        'id' => 1,
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $parentDay->id,
        'starts_at' => $parentDay->date->copy()->setTime(17, 0),
        'ends_at' => $parentDay->date->copy()->setTime(17, 10),
        'room' => 'A101',
        'is_reserved' => false,
    ]);

    $this->actingAs($user)
        ->from('/student/booking')
        ->post(route('parent-days.select'), [
            'parent_day_id' => $parentDay->id,
        ])
        ->assertRedirect('/student/booking');

    expect(session('parent_day_id'))->toBe($parentDay->id);
});
