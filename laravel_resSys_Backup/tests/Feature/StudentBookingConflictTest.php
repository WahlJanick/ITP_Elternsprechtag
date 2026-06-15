<?php

use App\Models\Student;
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
        $table->string('klasse')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->boolean('is_teacher')->default(false);
        $table->boolean('is_admin')->default(false);
        $table->integer('teacher_id')->nullable();
        $table->timestamps();
    });

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
        $table->string('kuerzel')->nullable();
        $table->json('classes');
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

    $this->studentUser = User::factory()->create([
        'name' => 'Test Student',
        'klasse' => '3AHIT',
        'is_teacher' => false,
        'is_admin' => false,
    ]);

    Student::create([
        'student_id' => $this->studentUser->id,
        'first_name' => 'Test',
        'last_name' => 'Student',
        'class_name' => '3AHIT',
    ]);

    Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Anna',
        'last_name' => 'Alt',
        'kuerzel' => 'ALT',
        'classes' => ['3AHIT'],
    ]);

    Teacher::create([
        'teacher_id' => 2,
        'first_name' => 'Nora',
        'last_name' => 'Neu',
        'kuerzel' => 'NEU',
        'classes' => ['3AHIT'],
    ]);
});

test('an overlapping booking at the same parent day is rejected', function () {
    createBookingConflictTimeslots($this->studentUser->id);

    $this->actingAs($this->studentUser)
        ->from('/student/teachers/nora-neu-2')
        ->post(route('student.timeslots.book'), ['timeslot_id' => 2])
        ->assertRedirect('/student/teachers/nora-neu-2')
        ->assertSessionHas('error', 'Du hast zu dieser Uhrzeit bereits einen anderen Termin.');

    expect(Timeslot::find(1)->is_reserved)->toBeTrue()
        ->and(Timeslot::find(2)->is_reserved)->toBeFalse();
});

test('appointments that only touch at their boundaries do not conflict', function () {
    createBookingConflictTimeslots($this->studentUser->id, '17:10');

    $this->actingAs($this->studentUser)
        ->post(route('student.timeslots.book'), ['timeslot_id' => 2])
        ->assertRedirect(route('student.bookings'));

    expect(Timeslot::find(1)->is_reserved)->toBeTrue()
        ->and(Timeslot::find(2)->is_reserved)->toBeTrue();
});

test('a second appointment with the same teacher at the same parent day is rejected', function () {
    $date = now()->addWeek()->format('Y-m-d');

    Timeslot::create([
        'id' => 1,
        'teacher_id' => 1,
        'parent_day_id' => 10,
        'student_id' => $this->studentUser->id,
        'starts_at' => "{$date} 17:00:00",
        'ends_at' => "{$date} 17:10:00",
        'room' => 'A101',
        'is_reserved' => true,
    ]);

    Timeslot::create([
        'id' => 2,
        'teacher_id' => 1,
        'parent_day_id' => 10,
        'starts_at' => "{$date} 17:20:00",
        'ends_at' => "{$date} 17:30:00",
        'room' => 'A101',
        'is_reserved' => false,
    ]);

    $this->actingAs($this->studentUser)
        ->from('/student/teachers/anna-alt-1')
        ->post(route('student.timeslots.book'), ['timeslot_id' => 2])
        ->assertRedirect('/student/teachers/anna-alt-1')
        ->assertSessionHas(
            'error',
            'Du hast bei diesem Lehrer für diesen Elternsprechtag bereits einen Termin.'
        );

    expect(Timeslot::find(1)->is_reserved)->toBeTrue()
        ->and(Timeslot::find(2)->is_reserved)->toBeFalse();
});

test('the same teacher can be booked on a different parent day', function () {
    $date = now()->addWeek()->format('Y-m-d');

    Timeslot::create([
        'id' => 1,
        'teacher_id' => 1,
        'parent_day_id' => 10,
        'student_id' => $this->studentUser->id,
        'starts_at' => "{$date} 17:00:00",
        'ends_at' => "{$date} 17:10:00",
        'room' => 'A101',
        'is_reserved' => true,
    ]);

    Timeslot::create([
        'id' => 2,
        'teacher_id' => 1,
        'parent_day_id' => 11,
        'starts_at' => "{$date} 17:00:00",
        'ends_at' => "{$date} 17:10:00",
        'room' => 'A101',
        'is_reserved' => false,
    ]);

    $this->actingAs($this->studentUser)
        ->post(route('student.timeslots.book'), ['timeslot_id' => 2])
        ->assertRedirect(route('student.bookings'));

    expect(Timeslot::find(1)->is_reserved)->toBeTrue()
        ->and(Timeslot::find(2)->is_reserved)->toBeTrue()
        ->and(Timeslot::find(2)->student_id)->toBe($this->studentUser->id);
});

function createBookingConflictTimeslots(int $studentId, string $newStart = '17:05'): void
{
    $date = now()->addWeek()->format('Y-m-d');

    Timeslot::create([
        'id' => 1,
        'teacher_id' => 1,
        'parent_day_id' => 10,
        'student_id' => $studentId,
        'starts_at' => "{$date} 17:00:00",
        'ends_at' => "{$date} 17:10:00",
        'room' => 'A101',
        'is_reserved' => true,
    ]);

    Timeslot::create([
        'id' => 2,
        'teacher_id' => 2,
        'parent_day_id' => 10,
        'starts_at' => "{$date} {$newStart}:00",
        'ends_at' => "{$date} 17:15:00",
        'room' => 'B202',
        'is_reserved' => false,
    ]);
}
