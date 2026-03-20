<?php

namespace App\Http\Controllers;

use App\Models\BannedWord;
use Illuminate\Http\Request;

class BannedWordController extends Controller
{
    public function index(Request $request)
    {
        $query = BannedWord::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('word', 'like', "%$search%");
        }

        $bannedWords = $query->paginate(10);

        return view('banned_words.index', compact('bannedWords'));
    }

    public function create(Request $request)
    {
        $data = ['exito' => ''];
        $bannedWord = new BannedWord();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'word' => 'required|string|unique:banned_words,word'
            ]);

            $bannedWord->word = $request->input('word');
            $bannedWord->save();

            $data['exito'] = 'Palabra vetada añadida correctamente';
        }


        return view('banned_words.create', [
            'datos'      => $data,
            'bannedWord' => $bannedWord,
            'disabled'   => '',
            'oper'       => 'create'
        ]);
    }

    public function edit(Request $request, $id)
    {
        $bannedWord = BannedWord::find($id);
        $disabled = '';
        $datos['exito'] = '';

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'word' => 'required|string|unique:banned_words,word,'.$bannedWord->id
            ]);

            $bannedWord->word = $request->input('word');
            $bannedWord->save();

            $datos['exito'] = 'Operación realizada correctamente';
            $disabled = 'disabled';


        }

        return view('banned_words.create', [
            'bannedWord' => $bannedWord,
            'datos'      => $datos,
            'disabled'   => $disabled,
            'oper'       => 'edit'
        ]);
    }

    public function destroy(Request $request, $id = '')
    {
        $bannedWord = BannedWord::find($id);

        if (!$bannedWord) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Palabra no encontrada'], 404);
            }
        }

        if ($request->isMethod('post')) {
            $bannedWord->delete();

            if ($request->ajax()) {
                return response()->json([
                    'exito' => 'Palabra eliminada correctamente'
                ]);
            }
            return redirect()->route('admin.index');
        }

        $datos = ['exito' => ''];
        
        return view('banned_words.create', [
            'bannedWord' => $bannedWord,
            'datos'      => $datos,
            'disabled'   => 'disabled',
            'oper'       => 'destroy'
        ]);
    }
}
