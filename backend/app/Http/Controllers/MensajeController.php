<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MensajeController extends Controller
{

    public function store(Request $request, Group $group): JsonResponse
    {
        $validated = $request->validate([
            'contenido'  => 'required|string|max:5000',
            'id_usuario' => 'required|exists:users,id',
        ]);


        $isParticipant = $group->students()->where('users.id', $validated['id_usuario'])->exists()
                      || $group->tutor_id === (int) $validated['id_usuario']
                      || $group->subjectsWithTeachers()
                               ->wherePivot('user_id', $validated['id_usuario'])
                               ->exists();

        if (!$isParticipant) {
            return response()->json([
                'message' => 'El usuario no es participante de este grupo.',
            ], 403);
        }

        $mensaje = Message::create([
            'user_id'  => $validated['id_usuario'],
            'group_id' => $group->id,
            'content'  => $validated['contenido'],
        ]);

        $mensaje->load('user:id,name,last_name,profile_picture,role');

        return response()->json([
            'message' => 'Mensaje guardado correctamente.',
            'data'    => [
                'id'        => $mensaje->id,
                'contenido' => $mensaje->content,
                'usuario'   => [
                    'id'              => $mensaje->user->id,
                    'nombre'          => $mensaje->user->name,
                    'apellido'        => $mensaje->user->last_name,
                    'foto_perfil'     => $mensaje->user->profile_picture,
                    'rol'             => $mensaje->user->role,
                ],
                'grupo_id'  => $mensaje->group_id,
                'fecha_hora' => $mensaje->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function index(Group $group): JsonResponse
    {
        $mensajes = Message::where('group_id', $group->id)
            ->with('user:id,name,last_name,profile_picture,role')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Message $msg) {
                return [
                    'id'        => $msg->id,
                    'contenido' => $msg->content,
                    'usuario'   => [
                        'id'              => $msg->user->id,
                        'nombre'          => $msg->user->name,
                        'apellido'        => $msg->user->last_name,
                        'foto_perfil'     => $msg->user->profile_picture,
                        'rol'             => $msg->user->role,
                    ],
                    'grupo_id'  => $msg->group_id,
                    'fecha_hora' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'grupo'    => [
                'id'     => $group->id,
                'nombre' => $group->name,
            ],
            'mensajes' => $mensajes,
            'total'    => $mensajes->count(),
        ]);
    }
}
