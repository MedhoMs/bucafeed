<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return Question::with(['user', 'tags'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'content' => 'required|string',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $question = Question::create($validated);
        
        if (isset($validated['tags'])) {
            $question->tags()->attach($validated['tags']);
        }

        return $question;
    }

    public function show(Question $question)
    {
        return $question->load(['user', 'tags', 'answers', 'aiValidations']);
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'title' => 'string',
            'content' => 'string',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $question->update($validated);

        if (isset($validated['tags'])) {
            $question->tags()->sync($validated['tags']);
        }

        return $question;
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return response()->noContent();
    }
}

