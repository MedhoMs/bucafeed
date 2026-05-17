<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Find or create a private chat between the authenticated user and another user.
     */
    public function findOrCreate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $authUser = $request->user();
        $otherUserId = $request->user_id;

        // Search for a private chat that contains both users
        $chat = Chat::where('type', 'private')
            ->whereHas('users', function($q) use ($authUser) {
                $q->where('users.id', $authUser->id);
            })
            ->whereHas('users', function($q) use ($otherUserId) {
                $q->where('users.id', $otherUserId);
            })
            ->first();

        if (!$chat) {
            // Create a new private chat
            $chat = Chat::create([
                'type' => 'private',
                'educational_center_id' => $authUser->educational_center_id
            ]);

            // Attach both users
            $chat->users()->attach([$authUser->id, $otherUserId]);
        }

        return response()->json($chat);
    }

    /**
     * Get messages for a specific chat.
     */
    public function getMessages(Chat $chat)
    {
        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($chat) {
                return [
                    'id' => $msg->id,
                    'chat_id' => $chat->id,
                    'type' => $msg->message_type,
                    'content' => $msg->content,
                    'file_name' => $msg->file_name,
                    'metadata' => $msg->metadata,
                    'sender' => $msg->user_id,
                    'user_name' => $msg->user ? $msg->user->name : 'Usuario',
                    'created_at' => $msg->created_at,
                ];
            });

        return response()->json($messages);
    }

    /**
     * Store a new message in a chat.
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
            'type' => 'required|string|in:text,image,pdf,call',
            'file_name' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $message = $chat->messages()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'] ?? '',
            'message_type' => $validated['type'],
            'file_name' => $validated['file_name'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $message->load('user');

        $otherUser = $chat->users()->where('users.id', '!=', $request->user()->id)->first();
        if ($otherUser) {
            $snippet = $message->content;
            if ($validated['type'] === 'call') {
                $snippet = 'Te ha invitado a una videollamada';
            } else if ($validated['type'] === 'image') {
                $snippet = '[Imagen]';
            } else if ($validated['type'] === 'pdf') {
                $snippet = '[Archivo PDF]';
            }

            $notif = Notification::create([
                'user_id' => $otherUser->id,
                'type' => 'private_message',
                'data' => [
                    'chat_id' => $chat->id,
                    'message_id' => $message->id,
                    'sender_id' => $message->user_id,
                    'sender_name' => $message->user->name . ' ' . ($message->user->last_name ?? ''),
                    'snippet' => mb_substr($snippet, 0, 100),
                ],
            ]);
            Notification::broadcast($otherUser->id, $notif->toArray());

            // Enviar correo si es una llamada
            if ($validated['type'] === 'call' && $otherUser->email) {
                try {
                    \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($otherUser, $notif) {
                        $message->to($otherUser->email)
                            ->subject('Invitación a Videollamada - TelamoNet')
                            ->html("
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2>¡Hola, {$otherUser->name}!</h2>
                                    <p><b>{$notif->data['sender_name']}</b> te ha invitado a una videollamada en TelamoNet.</p>
                                    <p>Entra en la plataforma para unirte a la sesión.</p>
                                    <br>
                                    <p style='font-size: 12px; color: #666;'>Este es un mensaje automático, por favor no respondas.</p>
                                </div>
                            ");
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error enviando correo de videollamada: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'id' => $message->id,
            'chat_id' => $chat->id,
            'type' => $message->message_type,
            'content' => $message->content,
            'file_name' => $message->file_name,
            'metadata' => $message->metadata,
            'sender' => $message->user_id,
            'user_name' => $message->user ? $message->user->name : 'Usuario',
            'created_at' => $message->created_at,
        ], 201);
    }
}
