<?php

namespace App\Http\Controllers;

use App\Models\Timeslot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TimeslotController extends Controller
{
    public function index(): JsonResponse
    {
        $timeslots = Timeslot::with(['teacher', 'student'])->get();
        return response()->json($timeslots);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,teacher_id',
            'parent_day_id' => 'nullable|integer|exists:parent_days,id',
            'student_id' => 'nullable|integer|exists:students,student_id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'room' => 'required|string|max:255',
            'is_reserved' => 'boolean',
            'day' => 'nullable|date',
        ]);

        $timeslot = Timeslot::create($validated);
        return response()->json($timeslot, 201);
    }

    public function show(Timeslot $timeslot): JsonResponse
    {
        return response()->json($timeslot->load(['teacher', 'student']));
    }

    public function update(Request $request, Timeslot $timeslot): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'integer|exists:teachers,teacher_id',
            'parent_day_id' => 'nullable|integer|exists:parent_days,id',
            'student_id' => 'nullable|integer|exists:students,student_id',
            'starts_at' => 'date',
            'ends_at' => 'date|after:starts_at',
            'room' => 'string|max:255',
            'is_reserved' => 'boolean',
            'day' => 'nullable|date',
        ]);

        $timeslot->update($validated);
        return response()->json($timeslot);
    }

    public function destroy(Timeslot $timeslot): JsonResponse
    {
        $timeslot->delete();
        return response()->json(['message' => 'Termin gelöscht'], 204);
    }
}
