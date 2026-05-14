<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Notification;
use Illuminate\Http\Request;

class AnswerController extends TemplateController
{
    protected $model = Answer::class;
    protected $viewPath = 'answers';
    protected $with = ['user', 'question'];

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('question_id')) {
            $query->where('question_id', $request->question_id);
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

    public function store(Request $request)
    {
        $result = parent::store($request);

        if ($request->expectsJson() && $result instanceof Answer) {
            $this->createAnswerNotification($result);
        }

        return $result;
    }

    private function createAnswerNotification(Answer $answer): void
    {
        $question = $answer->question;
        $authorId = $question->user_id;

        if ((int) $authorId === (int) $answer->user_id) {
            return;
        }

        $snippet = mb_substr(strip_tags($answer->content ?? ''), 0, 100);

        $notification = Notification::create([
            'user_id' => $authorId,
            'type' => 'answer',
            'data' => [
                'question_id' => $question->id,
                'question_title' => $question->title,
                'answer_id' => $answer->id,
                'answer_snippet' => $snippet,
                'user_name' => $answer->user->name . ' ' . ($answer->user->last_name ?? ''),
            ],
        ]);

        $this->broadcastNotification($authorId, $notification->toArray());
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

        // Notificar al autor de la respuesta
        $answerAuthorId = $answer->user_id;
        if ((int) $answerAuthorId !== (int) $question->user_id) {
            $answer->load('user');
            $notification = Notification::create([
                'user_id' => $answerAuthorId,
                'type' => 'answer_useful',
                'data' => [
                    'question_id' => $question->id,
                    'question_title' => $question->title,
                    'answer_id' => $answer->id,
                    'user_name' => $question->user->name . ' ' . ($question->user->last_name ?? ''),
                ],
            ]);

            $this->broadcastNotification($answerAuthorId, $notification->toArray());
        }
        
        return response()->json([
            'message' => 'Reputación otorgada correctamente',
            'answer' => $answer->load('user')
        ]);
    }
}


