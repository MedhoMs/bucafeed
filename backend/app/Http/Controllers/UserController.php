<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['educationalCenter', 'teacher', 'student'])->get();
        return view('users', compact('users'));
    }

    public function store(Request $request)
    {
        try {
        $validated = $request->validate([
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
            'national_id' => 'required|string|unique:users',
            'educational_center_id' => 'nullable|exists:educational_centers,id',
            'education_level' => 'nullable|in:primary,secondary',
            'institution_name' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        return User::create($validated);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(User $user)
    {
        return $user->load(['educationalCenter', 'teacher', 'student']);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'string',
            'last_name' => 'string',
            'email' => 'email|unique:users,email,' . $user->id,
            'role' => 'string',
            'national_id' => 'string|unique:users,national_id,' . $user->id,
            'educational_center_id' => 'nullable|exists:educational_centers,id',
            'education_level' => 'nullable|in:primary,secondary',
            'institution_name' => 'nullable|string',
        ]);

        $user->update($validated);

        return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}

