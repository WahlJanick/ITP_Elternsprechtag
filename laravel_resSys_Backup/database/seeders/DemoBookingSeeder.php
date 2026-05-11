<?php

namespace Database\Seeders;

use App\Models\Timeslot;
use Illuminate\Database\Seeder;

class DemoBookingSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = [
            ['timeslot_id' => 1, 'student_id' => 101],
            ['timeslot_id' => 14, 'student_id' => 101],
            ['timeslot_id' => 27, 'student_id' => 102],
            ['timeslot_id' => 40, 'student_id' => 103],
            ['timeslot_id' => 53, 'student_id' => 104],
            ['timeslot_id' => 66, 'student_id' => 105],
            ['timeslot_id' => 79, 'student_id' => 106],
        ];

        foreach ($bookings as $booking) {
            Timeslot::where('id', $booking['timeslot_id'])->update([
                'student_id' => $booking['student_id'],
                'is_reserved' => true,
            ]);
        }
    }
}
