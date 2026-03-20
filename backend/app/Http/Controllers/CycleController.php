<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    public function index(Request $request)
    {
        $query = Cycle::orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('level', 'like', "%$search%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $cycles = $query->paginate(10);
        $levels = Cycle::whereNotNull('level')->pluck('level', 'level')->unique()->toArray();
        
        return view('admin.global_cycles.index', [
            'cycles' => $cycles,
            'levels' => $levels,
            'total' => Cycle::count() // Necesario para el contador superior
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:cycles,name']);
        
        Cycle::create([
            'name' => $request->input('name', 'General'),
            'area' => $request->input('area'),
            'level' => $request->input('level'),
        ]);

        if ($request->ajax()) {
            return $this->index($request);
        }
        return back();
    }

    public function destroy(Request $request, $id)
    {
        $cycle = Cycle::findOrFail($id);
        $cycle->delete();

        if ($request->ajax()) {
            $cycles = Cycle::orderBy('name')->get();
            return view('admin.global_cycles.index', compact('cycles'));
        }
        return back();
    }
}
