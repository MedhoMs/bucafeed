<?php

namespace App\Http\Controllers;

use App\Models\FacialVerification;
use Illuminate\Http\Request;

class FacialVerificationController extends Controller
{
    public function index()
    {
        return FacialVerification::with('user')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'image_hash' => 'required|string',
            'is_validated' => 'boolean',
            'verified_at' => 'nullable|date',
        ]);

        return FacialVerification::create($validated);
    }

    public function show(FacialVerification $facialVerification)
    {
        return $facialVerification->load('user');
    }

    public function update(Request $request, FacialVerification $facialVerification)
    {
        $validated = $request->validate([
            'is_validated' => 'boolean',
            'verified_at' => 'nullable|date',
        ]);

        $facialVerification->update($validated);

        return $facialVerification;
    }

    public function destroy(FacialVerification $facialVerification)
    {
        $facialVerification->delete();
        return response()->noContent();
    }
}

