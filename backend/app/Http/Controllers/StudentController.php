<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return Student::with(['user', 'educationalCenter'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'educational_center_id' => 'required|exists:educational_centers,id',
            'course' => 'required|string',
        ]);

        return Student::create($validated);
    }

    public function show(Student $student)
    {
        return $student->load(['user', 'educationalCenter']);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'course' => 'string',
            'educational_center_id' => 'exists:educational_centers,id',
        ]);

        $student->update($validated);

        return $student;
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->noContent();
    }
}

