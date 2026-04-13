<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return Teacher::with(['user', 'educationalCenter'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'educational_center_id' => 'required|exists:educational_centers,id',
            'specialty' => 'required|string',
        ]);

        return Teacher::create($validated);
    }

    public function show(Teacher $teacher)
    {
        return $teacher->load(['user', 'educationalCenter']);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'specialty' => 'string',
            'educational_center_id' => 'exists:educational_centers,id',
        ]);

        $teacher->update($validated);

        return $teacher;
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return response()->noContent();
    }
}

