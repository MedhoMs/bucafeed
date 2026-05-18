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
        // Enforce that teachers and center admins cannot answer questions
        $user = auth()->user();
        if ($user && in_array($user->role, ['Teacher', 'EI'])) {
            $msg = $user->role === 'Teacher' 
                ? 'Los profesores no pueden responder a las preguntas.' 
                : 'Los administradores de centros no pueden responder a las preguntas.';
            return response()->json(['message' => $msg], 403);
        }

        $requestUserId = $request->input('user_id');
        if ($requestUserId) {
            $reqUser = \App\Models\User::find($requestUserId);
            if ($reqUser && in_array($reqUser->role, ['Teacher', 'EI'])) {
                $msg = $reqUser->role === 'Teacher' 
                    ? 'Los profesores no pueden responder a las preguntas.' 
                    : 'Los administradores de centros no pueden responder a las preguntas.';
                return response()->json(['message' => $msg], 403);
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
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $question = $answer->question;
        if (!$question) {
            return response()->json(['message' => 'Pregunta no encontrada.'], 404);
        }

        if ((int)$question->user_id !== (int)$user->id) {
            return response()->json(['message' => 'Solo el dueño de la pregunta puede marcar una respuesta como útil.'], 403);
        }

        if (!in_array($user->role, ['Student', 'Teacher'])) {
            return response()->json(['message' => 'Solo los alumnos o profesores dueños de la pregunta pueden dar reputación.'], 403);
        }

        if ((int)$answer->user_id === (int)$user->id) {
            return response()->json(['message' => 'No puedes marcar tu propia respuesta como útil.'], 400);
        }

        if ($answer->is_useful) {
            return response()->json(['message' => 'La respuesta ya está marcada como útil.'], 400);
        }

        // Mark as useful
        $answer->is_useful = true;
        $answer->save();

        // Increment author's reputation
        $answerAuthor = $answer->user;
        if ($answerAuthor) {
            $answerAuthor->reputation = ($answerAuthor->reputation || 0) + 50;
            $answerAuthor->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Respuesta marcada como útil y reputación otorgada.',
            'answer' => $answer
        ]);
    }

    public function unmarkAsUseful(Answer $answer)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $question = $answer->question;
        if (!$question) {
            return response()->json(['message' => 'Pregunta no encontrada.'], 404);
        }

        if ((int)$question->user_id !== (int)$user->id) {
            return response()->json(['message' => 'Solo el dueño de la pregunta puede desmarcar una respuesta.'], 403);
        }

        if (!in_array($user->role, ['Student', 'Teacher'])) {
            return response()->json(['message' => 'Solo los alumnos o profesores dueños de la pregunta pueden retirar reputación.'], 403);
        }

        if (!$answer->is_useful) {
            return response()->json(['message' => 'La respuesta no estaba marcada como útil.'], 400);
        }

        // Unmark as useful
        $answer->is_useful = false;
        $answer->save();

        // Decrement author's reputation
        $answerAuthor = $answer->user;
        if ($answerAuthor) {
            $answerAuthor->reputation = max(0, ($answerAuthor->reputation || 0) - 50);
            $answerAuthor->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reconocimiento retirado y reputación descontada.',
            'answer' => $answer
        ]);
    }
}
