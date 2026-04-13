<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return Chat::with(['users', 'educationalCenter'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'educational_center_id' => 'nullable|exists:educational_centers,id',
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $chat = Chat::create($validated);

        if (isset($validated['users'])) {
            $chat->users()->attach($validated['users']);
        }

        return $chat;
    }

    public function show(Chat $chat)
    {
        return $chat->load(['users', 'messages', 'educationalCenter']);
    }

    public function update(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'type' => 'string',
            'educational_center_id' => 'nullable|exists:educational_centers,id',
            'users' => 'array', // For syncing users if needed
        ]);

        $chat->update($validated);

        if (isset($validated['users'])) {
            $chat->users()->sync($validated['users']);
        }

        return $chat;
    }

    public function destroy(Chat $chat)
    {
        $chat->delete();
        return response()->noContent();
    }
}

