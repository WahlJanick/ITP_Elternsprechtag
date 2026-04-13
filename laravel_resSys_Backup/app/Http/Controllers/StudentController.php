<?php
namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Timeslot;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{

//Seite 1 – "Mögliche Buchungen"
    public function dashboard(): View
    {
        /** @var \App\Models\Student $student */
        $student = auth()->user();

        $teachers = Teacher::whereJsonContains('classes', $student->class_name)
            ->whereHas('timeslots', fn($q) => $q->where('is_reserved', false))
            ->get();

        // Welche Lehrer hat der Schüler bereits gebucht? → Slots sperren
        $bookedTeacherIds = Timeslot::where('student_id', $student->student_id)
            ->pluck('teacher_id')
            ->toArray();

        return view('students.dashboard', compact('teachers', 'bookedTeacherIds'));
    }

    
    //Timeslot-Panel eines Lehrers (durch AJAX-Request von der Dashboard-Seite geladen)
     
    public function teacherTimeslots(Teacher $teacher): View
    {
        /** @var \App\Models\Student $student */
        $student = auth()->user();

        $freeTimeslots = $teacher->timeslots()
            ->where('is_reserved', false)
            ->orderBy('starts_at')
            ->get();

        $alreadyBooked = Timeslot::where('teacher_id', $teacher->teacher_id)
            ->where('student_id', $student->student_id)
            ->exists();

        return view('students.partials.timeslot-panel', compact(
            'teacher',
            'freeTimeslots',
            'alreadyBooked'
        ));
    }
//Seite 2 – "Gebuchte Timeslots"
    public function myBookings(): View
    {
        /** @var \App\Models\Student $student */
        $student = auth()->user();

        $bookings = Timeslot::with('teacher')
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->orderBy('starts_at')
            ->get();

        return view('students.my-bookings', compact('bookings'));
    }
}