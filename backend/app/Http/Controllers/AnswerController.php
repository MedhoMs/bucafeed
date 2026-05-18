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

    public function store(Request $request)
    {
        // Enforce that teachers cannot answer questions
        $user = auth()->user();
        if ($user && $user->role === 'Teacher') {
            return response()->json(['message' => 'Los profesores no pueden responder a las preguntas.'], 403);
        }

        $requestUserId = $request->input('user_id');
        if ($requestUserId) {
            $reqUser = \App\Models\User::find($requestUserId);
            if ($reqUser && $reqUser->role === 'Teacher') {
                return response()->json(['message' => 'Los profesores no pueden responder a las preguntas.'], 403);
            }
        }

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
        return response()->json(['message' => 'La función de marcar respuestas como reconocidas está deshabilitada.'], 400);
    }

    public function unmarkAsUseful(Answer $answer)
    {
        return response()->json(['message' => 'La función de retirar el reconocimiento está deshabilitada.'], 400);
    }
}
