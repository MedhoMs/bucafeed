<?php

namespace App\Http\Controllers;

use App\Models\BannedWord;
use Illuminate\Http\Request;

class BannedWordController extends Controller
{
    public function index()
    {
        return BannedWord::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|unique:banned_words,word',
        ]);

        return BannedWord::create($validated);
    }

    public function destroy(BannedWord $bannedWord)
    {
        $bannedWord->delete();
        return response()->noContent();
    }
}

