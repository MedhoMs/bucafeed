<?php

namespace App\Http\Controllers;

use App\Models\EducationalCenter;
use Illuminate\Http\Request;

class EducationalCenterController extends Controller
{
    public function index()
    {
        return EducationalCenter::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'location' => 'required|string',
        ]);

        return EducationalCenter::create($validated);
    }

    public function show(EducationalCenter $educationalCenter)
    {
        return $educationalCenter;
    }

    public function update(Request $request, EducationalCenter $educationalCenter)
    {
        $validated = $request->validate([
            'name' => 'string',
            'type' => 'string',
            'location' => 'string',
        ]);

        $educationalCenter->update($validated);

        return $educationalCenter;
    }

    public function destroy(EducationalCenter $educationalCenter)
    {
        $educationalCenter->delete();
        return response()->noContent();
    }
}

