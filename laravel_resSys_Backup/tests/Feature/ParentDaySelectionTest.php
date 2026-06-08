<?php

use App\Models\ParentDay;
use App\Models\Teacher;
use App\Models\Timeslot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
        'day' => $parentDay->date,
    ]);

    $this->actingAs($user)
        ->from('/student/booking')
        ->post(route('parent-days.select'), [
            'parent_day_id' => $parentDay->id,
        ])
        ->assertRedirect('/student/booking');

    expect(session('parent_day_id'))->toBe($parentDay->id);
});
