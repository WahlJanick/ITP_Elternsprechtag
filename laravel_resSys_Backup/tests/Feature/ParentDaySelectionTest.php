<?php

use App\Http\Controllers\PortalController;
use App\Models\ParentDay;
use App\Models\Teacher;
use App\Models\TeacherParentDaySetting;
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
        $table->string('klasse')->nullable();
        $table->timestamps();
    });

    Schema::create('teachers', function (Blueprint $table) {
        $table->increments('teacher_id');
        $table->string('first_name');
        $table->string('last_name');
        $table->json('classes');
    });

    Schema::create('parent_days', function (Blueprint $table) {
        $table->id();
        $table->date('date')->unique();
        $table->string('label')->nullable();
        $table->boolean('is_active_for_students')->default(true);
        $table->timestamps();
    });

    Schema::create('timeslots', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('teacher_id');
        $table->foreignId('parent_day_id')->nullable();
        $table->integer('student_id')->nullable();
        $table->dateTime('starts_at');
        $table->dateTime('ends_at');
        $table->string('room');
        $table->boolean('is_reserved')->default(false);
        $table->unique(['teacher_id', 'parent_day_id', 'starts_at']);
    });

    Schema::create('teacher_parent_day_settings', function (Blueprint $table) {
        $table->id();
        $table->integer('teacher_id');
        $table->foreignId('parent_day_id');
        $table->integer('timeslot_duration')->nullable();
        $table->timestamp('duration_changed_at')->nullable();
        $table->boolean('duration_changed_by_teacher')->default(false);
        $table->timestamps();
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

test('a student sees only parent days with a bookable or already booked appointment', function () {
    $user = User::factory()->create([
        'klasse' => '3AHIT',
        'is_teacher' => false,
        'is_admin' => false,
    ]);

    $matchingTeacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Passende',
        'last_name' => 'Lehrerin',
        'classes' => ['3AHIT'],
    ]);
    $otherTeacher = Teacher::create([
        'teacher_id' => 2,
        'first_name' => 'Andere',
        'last_name' => 'Lehrerin',
        'classes' => ['4BHIT'],
    ]);

    $bookableDay = ParentDay::create(['date' => now()->addWeek()->toDateString()]);
    $bookedDay = ParentDay::create(['date' => now()->addWeeks(2)->toDateString()]);
    $unavailableDay = ParentDay::create(['date' => now()->addWeeks(3)->toDateString()]);

    Timeslot::create([
        'id' => 1,
        'teacher_id' => $matchingTeacher->teacher_id,
        'parent_day_id' => $bookableDay->id,
        'starts_at' => $bookableDay->date->copy()->setTime(17, 0),
        'ends_at' => $bookableDay->date->copy()->setTime(17, 10),
        'room' => 'A101',
        'is_reserved' => false,
    ]);
    Timeslot::create([
        'id' => 2,
        'teacher_id' => $otherTeacher->teacher_id,
        'parent_day_id' => $bookedDay->id,
        'student_id' => $user->id,
        'starts_at' => $bookedDay->date->copy()->setTime(17, 0),
        'ends_at' => $bookedDay->date->copy()->setTime(17, 10),
        'room' => 'B201',
        'is_reserved' => true,
    ]);
    Timeslot::create([
        'id' => 3,
        'teacher_id' => $otherTeacher->teacher_id,
        'parent_day_id' => $unavailableDay->id,
        'starts_at' => $unavailableDay->date->copy()->setTime(17, 0),
        'ends_at' => $unavailableDay->date->copy()->setTime(17, 10),
        'room' => 'C301',
        'is_reserved' => false,
    ]);

    $this->actingAs($user);

    $controller = app(PortalController::class);
    $method = new ReflectionMethod($controller, 'availableParentDaysForCurrentUser');
    $parentDays = $method->invoke($controller);

    expect($parentDays->pluck('id')->all())
        ->toBe([$bookableDay->id, $bookedDay->id]);
});

test('an admin can hide a parent day from students', function () {
    $admin = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => true,
    ]);
    $student = User::factory()->create([
        'klasse' => '3AHIT',
        'is_teacher' => false,
        'is_admin' => false,
    ]);
    $teacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => ['3AHIT'],
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

    $this->actingAs($admin)
        ->post(route('admin.parent-days.student-access.update', $parentDay), [
            'is_active_for_students' => 0,
        ])
        ->assertRedirect(route('admin.dashboard').'#parent-day');

    expect($parentDay->fresh()->is_active_for_students)->toBeFalse();

    $this->actingAs($student);
    $controller = app(PortalController::class);
    $method = new ReflectionMethod($controller, 'availableParentDaysForCurrentUser');

    expect($method->invoke($controller))->toBeEmpty();
});

test('a teacher can select only parent days with actual timeslots', function () {
    $teacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);

    $user = User::factory()->create([
        'is_teacher' => true,
        'is_admin' => false,
        'teacher_id' => $teacher->teacher_id,
    ]);

    $assignedParentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);
    $otherParentDay = ParentDay::create([
        'date' => now()->addWeeks(2)->toDateString(),
    ]);

    Timeslot::create([
        'id' => 1,
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $assignedParentDay->id,
        'starts_at' => $assignedParentDay->date->copy()->setTime(17, 0),
        'ends_at' => $assignedParentDay->date->copy()->setTime(17, 10),
        'room' => 'A101',
        'is_reserved' => false,
    ]);

    TeacherParentDaySetting::create([
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $otherParentDay->id,
        'timeslot_duration' => 10,
    ]);

    $this->actingAs($user)
        ->from('/teacher/dashboard')
        ->post(route('parent-days.select'), [
            'parent_day_id' => $assignedParentDay->id,
        ])
        ->assertRedirect('/teacher/dashboard');

    expect(session('parent_day_id'))->toBe($assignedParentDay->id);

    $this->actingAs($user)
        ->from('/teacher/dashboard')
        ->post(route('parent-days.select'), [
            'parent_day_id' => $otherParentDay->id,
        ])
        ->assertRedirect('/teacher/dashboard')
        ->assertSessionHas('error');

    expect(session('parent_day_id'))->toBe($assignedParentDay->id);
});

test('an admin updates a teacher room only for the selected parent day', function () {
    $admin = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => true,
    ]);

    $teacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);

    $selectedParentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);
    $otherParentDay = ParentDay::create([
        'date' => now()->addWeeks(2)->toDateString(),
    ]);

    Timeslot::create([
        'id' => 1,
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $selectedParentDay->id,
        'starts_at' => $selectedParentDay->date->copy()->setTime(17, 0),
        'ends_at' => $selectedParentDay->date->copy()->setTime(17, 10),
        'room' => '',
        'is_reserved' => false,
    ]);
    Timeslot::create([
        'id' => 2,
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $otherParentDay->id,
        'starts_at' => $otherParentDay->date->copy()->setTime(17, 0),
        'ends_at' => $otherParentDay->date->copy()->setTime(17, 10),
        'room' => 'B202',
        'is_reserved' => false,
    ]);

    $this->actingAs($admin)
        ->withSession(['parent_day_id' => $selectedParentDay->id])
        ->post(route('admin.teachers.room.update', 'max-muster-1'), [
            'room' => 'B201',
        ])
        ->assertRedirect(route('admin.dashboard').'#teachers');

    expect(Timeslot::find(1)->room)->toBe('B201')
        ->and(Timeslot::find(2)->room)->toBe('B202')
        ->and(
            TeacherParentDaySetting::query()
                ->where('teacher_id', $teacher->teacher_id)
                ->where('parent_day_id', $selectedParentDay->id)
                ->value('room')
        )->toBe('B201');
});

test('teacher profiles are limited to the selected parent day', function () {
    $admin = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => true,
    ]);

    $selectedTeacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Aktive',
        'last_name' => 'Lehrerin',
        'classes' => [],
    ]);
    $otherTeacher = Teacher::create([
        'teacher_id' => 2,
        'first_name' => 'Frühere',
        'last_name' => 'Lehrerin',
        'classes' => [],
    ]);

    $selectedParentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);
    $otherParentDay = ParentDay::create([
        'date' => now()->addWeeks(2)->toDateString(),
    ]);

    TeacherParentDaySetting::create([
        'teacher_id' => $selectedTeacher->teacher_id,
        'parent_day_id' => $selectedParentDay->id,
        'timeslot_duration' => 10,
    ]);
    TeacherParentDaySetting::create([
        'teacher_id' => $otherTeacher->teacher_id,
        'parent_day_id' => $otherParentDay->id,
        'timeslot_duration' => 10,
    ]);

    $this->actingAs($admin)->withSession([
        'parent_day_id' => $selectedParentDay->id,
    ]);

    $controller = app(PortalController::class);
    $method = new ReflectionMethod($controller, 'allTeacherProfiles');
    $profiles = $method->invoke($controller);

    expect($profiles->pluck('reference_id')->all())
        ->toBe([$selectedTeacher->teacher_id]);
});

test('teacher profiles are empty when no parent day exists', function () {
    $admin = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => true,
    ]);

    Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);

    $this->actingAs($admin);

    $controller = app(PortalController::class);
    $method = new ReflectionMethod($controller, 'allTeacherProfiles');

    expect($method->invoke($controller))->toBeEmpty();
});

test('generating the same teacher timeslots twice does not create duplicates', function () {
    $teacher = Teacher::create([
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);
    $parentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);

    $controller = app(PortalController::class);
    $method = new ReflectionMethod($controller, 'createGeneratedTimeslotsForTeacher');

    $firstResult = $method->invoke(
        $controller,
        $teacher->teacher_id,
        $parentDay,
        '17:00',
        '17:30',
        10,
        'A101'
    );
    $secondResult = $method->invoke(
        $controller,
        $teacher->teacher_id,
        $parentDay,
        '17:00',
        '17:30',
        10,
        'A101'
    );

    expect($teacher->teacher_id)->toBeInt()
        ->and($firstResult)->toBe(['created' => 3, 'skipped' => 0])
        ->and($secondResult)->toBe(['created' => 0, 'skipped' => 3])
        ->and(Timeslot::query()->count())->toBe(3)
        ->and(Timeslot::query()->pluck('id')->unique()->count())->toBe(3);
});

test('manually adding an existing teacher assigns them to the selected parent day', function () {
    $admin = User::factory()->create([
        'is_teacher' => false,
        'is_admin' => true,
    ]);

    $teacher = Teacher::create([
        'teacher_id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Muster',
        'classes' => [],
    ]);
    User::factory()->create([
        'name' => 'Max Muster',
        'email' => 'max.muster@example.com',
        'is_teacher' => true,
        'is_admin' => false,
        'teacher_id' => $teacher->teacher_id,
    ]);

    $previousParentDay = ParentDay::create([
        'date' => now()->addWeek()->toDateString(),
    ]);
    $newParentDay = ParentDay::create([
        'date' => now()->addWeeks(2)->toDateString(),
    ]);

    TeacherParentDaySetting::create([
        'teacher_id' => $teacher->teacher_id,
        'parent_day_id' => $previousParentDay->id,
        'timeslot_duration' => 10,
    ]);

    $this->actingAs($admin)
        ->withSession(['parent_day_id' => $newParentDay->id])
        ->post(route('admin.teachers.accounts.store'), [
            'teacher_emails' => 'Max Muster <max.muster@example.com>',
            'timeslot_duration' => 10,
            'timeslot_start' => '17:00',
            'timeslot_end' => '17:20',
            'timeslot_room' => 'B201',
            'classes' => [],
        ])
        ->assertRedirect(route('admin.dashboard'));

    expect(
        TeacherParentDaySetting::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('parent_day_id', $newParentDay->id)
            ->exists()
    )->toBeTrue()
        ->and(
            Timeslot::query()
                ->where('teacher_id', $teacher->teacher_id)
                ->where('parent_day_id', $newParentDay->id)
                ->count()
        )->toBe(2);
});
