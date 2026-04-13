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
            $validator = Validator::make($request->all(), [
                'word' => 'required|string|unique:banned_words,word'
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('banned_words.create', [
                        'datos'      => $data,
                        'bannedWord' => $bannedWord,
                        'fields'     => $this->getBannedWordFields($bannedWord),
                        'disabled'   => '',
                        'oper'       => 'create'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $bannedWord->word = $request->input('word');
            $bannedWord->save();

            $data['exito'] = 'Palabra vetada añadida correctamente';
        }

        return view('banned_words.create', [
            'datos'      => $data,
            'bannedWord' => $bannedWord,
            'fields'     => $this->getBannedWordFields($bannedWord),
            'disabled'   => '',
            'oper'       => 'create'
        ]);
    }

    public function edit(Request $request, $id)
    {
        $bannedWord = BannedWord::findOrFail($id);
        $disabled = '';
        $datos['exito'] = '';

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'word' => 'required|string|unique:banned_words,word,'.$bannedWord->id
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('banned_words.create', [
                        'bannedWord' => $bannedWord,
                        'fields'     => $this->getBannedWordFields($bannedWord),
                        'datos'      => $datos,
                        'disabled'   => $disabled,
                        'oper'       => 'edit'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $bannedWord->word = $request->input('word');
            $bannedWord->save();

            $datos['exito'] = 'Operación realizada correctamente';
            $disabled = 'disabled';

            if ($request->ajax()) {
                return view('banned_words.create', [
                    'bannedWord' => $bannedWord,
                    'oper' => 'edit',
                    'disabled' => $disabled,
                    'datos' => $datos
                ]);
            }
        }

        return view('banned_words.create', [
            'bannedWord' => $bannedWord,
            'fields'     => $this->getBannedWordFields($bannedWord),
            'datos'      => $datos,
            'disabled'   => $disabled,
            'oper'       => 'edit'
        ]);
    }

    public function destroy(Request $request, $id = '')
    {
        $bannedWord = BannedWord::findOrFail($id);

        if ($request->isMethod('post')) {
            $bannedWord->delete();

            if ($request->ajax()) {
                return view('banned_words.create', [
                    'bannedWord' => $bannedWord,
                    'fields'     => $this->getBannedWordFields($bannedWord),
                    'datos'      => ['exito' => 'Palabra eliminada correctamente'],
                    'disabled'   => 'disabled',
                    'oper'       => 'destroy'
                ]);
            }
            return redirect()->route('admin.index');
        }

        $datos = ['exito' => ''];
        $disabled = 'disabled';
        
        return view('banned_words.create', [
            'bannedWord' => $bannedWord,
            'fields'     => $this->getBannedWordFields($bannedWord),
            'datos'      => $datos,
            'disabled'   => $disabled,
            'oper'       => 'destroy'
        ]);
    }

    /**
     * Define los campos para el formulario de palabras prohibidas.
     */
    protected function getBannedWordFields($bannedWord = null)
    {
        return [
            ['name' => 'word', 'label' => 'Palabra', 'placeholder' => 'Ej: spam', 'value' => old('word', $bannedWord->word ?? ''), 'required' => true, 'full' => true]
        ];
    }
}
