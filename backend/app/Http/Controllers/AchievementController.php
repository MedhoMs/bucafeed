<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index()
    {
        return Achievement::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'points_required' => 'required|integer',
        ]);

        return Achievement::create($validated);
    }

    public function show(Achievement $achievement)
    {
        return $achievement;
    }

    public function update(Request $request, Achievement $achievement)
    {
        $validated = $request->validate([
            'name' => 'string',
            'description' => 'string',
            'points_required' => 'integer',
        ]);

        $achievement->update($validated);

        return $achievement;
    }

    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        return response()->noContent();
    }
}







