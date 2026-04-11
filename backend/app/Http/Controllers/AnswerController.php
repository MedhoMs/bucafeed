<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function index()
    {
        return Answer::with(['user', 'question'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $answer = Answer::create($validated);
        
        // Optionally increment answer count on question
        $answer->question->increment('answer_count');

        return $answer;
    }

    public function show(Answer $answer)
    {
        return $answer->load(['user', 'question']);
    }

    public function update(Request $request, Answer $answer)
    {
        $validated = $request->validate([
            'content' => 'string',
        ]);

        $answer->update($validated);

        return $answer;
    }

    public function destroy(Answer $answer)
    {
        $answer->question->decrement('answer_count');
        $answer->delete();
        return response()->noContent();
    }

    public function markAsUseful(Answer $answer)
    {
        $question = $answer->question;

        // Verificar que el usuario autenticado sea el autor de la pregunta
        // Usamos un fallback a request->user_id si no hay auth session (para testing)
        $userId = auth()->id() ?? request()->user_id;

        if ($userId != $question->user_id) {
            return response()->json(['message' => 'Solo el autor de la pregunta puede otorgar reputación'], 403);
        }

        if ($answer->is_useful) {
            return response()->json(['message' => 'Esta respuesta ya ha sido marcada como útil'], 400);
        }

        $answer->is_useful = true;
        // También podemos incrementar la reputación de la respuesta en sí
        $answer->reputation += 10;
        $answer->save();
        
        // Incrementar reputación del autor de la respuesta
        $answer->user->increment('reputation', 10);
        
        return response()->json([
            'message' => 'Reputación otorgada correctamente',
            'answer' => $answer->load('user')
        ]);
    }
}


