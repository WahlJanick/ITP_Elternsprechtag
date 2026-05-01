<?php
namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Timeslot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    // Lehrer-Dashboard:
    public function dashboard(): View
    {
        /** @var \App\Models\Teacher $teacher */
        $teacher = auth()->user();

        $timeslots = Timeslot::where('teacher_id', $teacher->teacher_id)
            ->orderBy('starts_at')
            ->get();

        return view('teachers.dashboard', compact('timeslots'));
    }

    //Reservierte Timeslots eines Lehrers
    public function bookedTimeslots(): View
    {
        /** @var \App\Models\Teacher $teacher */
        $teacher = auth()->user();

        $bookedTimeslots = Timeslot::with('student')
            ->where('teacher_id', $teacher->teacher_id)
            ->where('is_reserved', true)
            ->orderBy('starts_at')
            ->get();

        return view('teachers.booked-timeslots', compact('bookedTimeslots'));
    }
}