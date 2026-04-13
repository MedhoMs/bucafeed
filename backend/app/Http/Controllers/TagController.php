<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        return Tag::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags,name',
        ]);

        return Tag::create($validated);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->noContent();
    }
}




