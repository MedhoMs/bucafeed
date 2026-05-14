<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends TemplateController
{
    protected $model = Answer::class;
    protected $viewPath = 'answers';
    protected $with = ['user', 'question'];

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('question_id')) {
            $query->where('question_id', $request->question_id)
                ->join('users', 'answers.user_id', '=', 'users.id')
                ->select('answers.*') // Evitar colisión de IDs
                ->orderBy('users.reputation', 'desc');
        }
        if ($request->filled('user_id')) {
            $query->where('answers.user_id', $request->user_id);
        }
        return $query;
    }

    protected function getFormFields($answer = null)
    {
        return [
            ['name' => 'content', 'type' => 'textarea', 'label' => 'Respuesta', 'value' => old('content', $answer->content ?? ''), 'required' => true, 'full' => true],
            ['name' => 'is_useful', 'type' => 'select', 'label' => '¿Es útil?', 'options' => [0 => 'No', 1 => 'Sí'], 'selectedValue' => old('is_useful', $answer->is_useful ?? 0)],
        ];
    }

    protected function rules($answer = null)
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|max:4096',
        ];
    }

    public function markAsUseful(Answer $answer)
    {
        $question = $answer->question;

        // Verificar que el usuario autenticado sea el autor de la pregunta
        $userId = auth()->id();

        if ($userId != $question->user_id) {
            return response()->json(['message' => 'Solo el autor de la pregunta puede otorgar reputación'], 403);
        }

        // El dueño de la respuesta no puede darse like a sí mismo (aunque sea el dueño de la pregunta)
        if ($answer->user_id == $userId) {
            return response()->json(['message' => 'No puedes otorgar reputación a tu propia respuesta'], 400);
        }

        if ($answer->is_useful) {
            return response()->json(['message' => 'Esta respuesta ya ha sido marcada como útil'], 400);
        }

        $answer->is_useful = true;
        // Incrementamos la reputación de la respuesta para destacar que fue útil
        $answer->reputation += 50;
        $answer->save();
        
        // Incrementar reputación del autor de la respuesta en 50 puntos
        $answer->user->increment('reputation', 50);
        
        return response()->json([
            'message' => 'Reputación otorgada correctamente',
            'answer' => $answer->load('user')
        ]);
    }

    public function unmarkAsUseful(Answer $answer)
    {
        $question = $answer->question;
        $userId = auth()->id();

        if ($userId != $question->user_id) {
            return response()->json(['message' => 'Solo el autor de la pregunta puede retirar la reputación'], 403);
        }

        if (!$answer->is_useful) {
            return response()->json(['message' => 'Esta respuesta no estaba marcada como útil'], 400);
        }

        $answer->is_useful = false;
        $answer->reputation -= 50;
        $answer->save();
        
        $answer->user->decrement('reputation', 50);
        
        return response()->json([
            'message' => 'Reputación retirada correctamente',
            'answer' => $answer->load('user')
        ]);
    }
}
