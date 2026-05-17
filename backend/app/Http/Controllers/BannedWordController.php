<?php

namespace App\Http\Controllers;

use App\Models\BannedWord;
use Illuminate\Http\Request;

class BannedWordController extends TemplateController
{
    protected $model = BannedWord::class;
    protected $viewPath = 'banned_words';

    public function __construct()
    {
        // Bloquear accesos directos por URL devolviendo 404 para que la ruta no exista en acceso directo
        if (!request()->ajax() && !request()->wantsJson()) {
            abort(404);
        }
    }

    protected function getFormFields($bannedWord = null)
    {
        return [
            ['name' => 'word', 'label' => 'Palabra prohibida', 'placeholder' => 'Ej: spam', 'value' => old('word', $bannedWord->word ?? ''), 'required' => true, 'full' => true]
        ];
    }

    protected function rules($bannedWord = null)
    {
        return [
            'word' => 'required|string|unique:banned_words,word,' . ($bannedWord->id ?? 'NULL')
        ];
    }
}
