<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Meeting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MeetingMessageController extends Controller
{
    public function store(Request $request, Meeting $meeting): JsonResponse
    {
        $validated = $request->validate([
            'contenido' => 'required|string|max:5000',
            'id_usuario' => 'required|exists:users,id',
        ]);

        $message = Message::create([
            'user_id' => $validated['id_usuario'],
            'meeting_id' => $meeting->id,
            'content' => $validated['contenido'],
        ]);

        $message->load('user:id,name,last_name,profile_picture,role');

        $recipients = collect();
        if ($meeting->group) {
            $meeting->group->load('students');
            $recipients = $meeting->group->students->pluck('id');
            if ($meeting->group->tutor_id) {
                $recipients->push($meeting->group->tutor_id);
            }
        }
        if ($recipients->isEmpty()) {
            $recipients = \App\Models\Message::where('meeting_id', $meeting->id)
                ->distinct()->pluck('user_id');
        }
        $recipients->push($meeting->teacher_id);
        $recipients = $recipients->unique()->filter(fn($id) => (int)$id !== (int)$message->user_id);

        $senderName = $message->user->name . ' ' . ($message->user->last_name ?? '');
        foreach ($recipients as $rid) {
            $notif = Notification::create([
                'user_id' => $rid,
                'type' => 'meeting_message',
                'data' => [
                    'meeting_id' => $meeting->id,
                    'meeting_name' => $meeting->name,
                    'message_id' => $message->id,
                    'sender_name' => $senderName,
                    'snippet' => mb_substr($message->content ?? '[Archivo]', 0, 100),
                ],
            ]);
            Notification::broadcast($rid, $notif->toArray());
        }

        return response()->json([
            'message' => 'Mensaje guardado correctamente.',
            'data' => [
                'id' => $message->id,
                'contenido' => $message->content,
                'usuario' => [
                    'id' => $message->user->id,
                    'nombre' => $message->user->name,
                    'apellido' => $message->user->last_name,
                    'foto_perfil' => $message->user->profile_picture,
                    'rol' => $message->user->role,
                ],
                'meeting_id' => $message->meeting_id,
                'fecha_hora' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function index(Meeting $meeting): JsonResponse
    {
        $messages = Message::where('meeting_id', $meeting->id)
            ->with('user:id,name,last_name,profile_picture,role')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Message $msg) {
                return [
                    'id' => $msg->id,
                    'contenido' => $msg->content,
                    'usuario' => [
                        'id' => $msg->user->id,
                        'nombre' => $msg->user->name,
                        'apellido' => $msg->user->last_name,
                        'foto_perfil' => $msg->user->profile_picture,
                        'rol' => $msg->user->role,
                    ],
                    'meeting_id' => $msg->meeting_id,
                    'fecha_hora' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'meeting' => [
                'id' => $meeting->id,
                'name' => $meeting->name,
            ],
            'mensajes' => $messages,
            'total' => $messages->count(),
        ]);
    }
}
