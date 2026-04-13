<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return Message::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        return Message::create($validated);
    }

    public function show(Message $message)
    {
        return $message;
    }

    public function update(Request $request, Message $message)
    {
        $validated = $request->validate([
            'content' => 'string',
        ]);

        $message->update($validated);

        return $message;
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return response()->noContent();
    }
}

