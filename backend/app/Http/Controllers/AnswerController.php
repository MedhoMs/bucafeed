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
}

