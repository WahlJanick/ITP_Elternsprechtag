<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Timeslot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TimeslotSeeder extends Seeder
{
    public function run(): void
    {
        $timeslots = [];
        $id = 1;
        $rooms = ['B201', 'B203', 'A104', 'Lab 2', '3AHMBA', '4AHIT'];

        Teacher::query()
            ->orderBy('teacher_id')
            ->get()
            ->each(function (Teacher $teacher) use (&$timeslots, &$id, $rooms) {
                $baseDay = Carbon::create(2026, 4, 28 + ($teacher->teacher_id % 3), 17, 0, 0);

                foreach (range(0, 5) as $offset) {
                    $startsAt = $baseDay->copy()->addMinutes($offset * 10);

                    $timeslots[] = [
                        'id' => $id++,
                        'teacher_id' => $teacher->teacher_id,
                        'student_id' => null,
                        'starts_at' => $startsAt->toDateTimeString(),
                        'ends_at' => $startsAt->copy()->addMinutes(10)->toDateTimeString(),
                        'room' => $rooms[($teacher->teacher_id + $offset) % count($rooms)],
                        'is_reserved' => false,
                        'day' => $startsAt->toDateString(),
                    ];
                }
            });

        if ($timeslots !== []) {
            Timeslot::upsert(
                $timeslots,
                ['id'],
                ['teacher_id', 'student_id', 'starts_at', 'ends_at', 'room', 'is_reserved', 'day']
            );
        }
    }
}
