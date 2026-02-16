<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        return Event::with('educationalCenter')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'educational_center_id' => 'required|exists:educational_centers,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location_maps' => 'required|string',
        ]);

        return Event::create($validated);
    }

    public function show(Event $event)
    {
        return $event->load('educationalCenter');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'educational_center_id' => 'exists:educational_centers,id',
            'title' => 'string',
            'description' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'location_maps' => 'string',
        ]);

        $event->update($validated);

        return $event;
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->noContent();
    }
}

