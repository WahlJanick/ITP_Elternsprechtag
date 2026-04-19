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
        $rooms = ['B201', 'B203', 'A104', 'Lab 2', 'C105', 'D201'];

        Teacher::query()
            ->orderBy('teacher_id')
            ->get()
            ->each(function (Teacher $teacher) use (&$timeslots, &$id, $rooms) {
                $baseDay = Carbon::create(2026, 4, 28, 17, 0, 0);

                // Jeder Lehrer hat einen festen Raum
                $teacherRoom = $rooms[$teacher->teacher_id % count($rooms)];

                // 12 Slots von 17:00 bis 19:00 (10-minütig)
                foreach (range(0, 11) as $offset) {
                    $startsAt = $baseDay->copy()->addMinutes($offset * 10);

                    $timeslots[] = [
                        'id' => $id++,
                        'teacher_id' => $teacher->teacher_id,
                        'student_id' => null,
                        'starts_at' => $startsAt->toDateTimeString(),
                        'ends_at' => $startsAt->copy()->addMinutes(10)->toDateTimeString(),
                        'room' => $teacherRoom,
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
