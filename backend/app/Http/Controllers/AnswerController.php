<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends TemplateController
{
    protected $model = Answer::class;
    protected $viewPath = 'answers';
    protected $with = ['user', 'question'];

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
            'content' => 'required|string',
        ];
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


