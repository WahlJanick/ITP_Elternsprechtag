<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    public function index(): JsonResponse
    {
        $teachers = Teacher::all();
        return response()->json($teachers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|integer|unique:teachers,teacher_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'classes' => 'required|array',
        ]);

        $teacher = Teacher::create($validated);
        return response()->json($teacher, 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load('timeslots'));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'classes' => 'array',
        ]);

        $teacher->update($validated);
        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();
        return response()->json(['message' => 'Teacher deleted'], 204);
    }
}
