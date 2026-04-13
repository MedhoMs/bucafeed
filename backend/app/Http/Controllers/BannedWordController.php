<?php

namespace App\Http\Controllers;

use App\Models\BannedWord;
use Illuminate\Http\Request;

class BannedWordController extends TemplateController
{
    protected $model = BannedWord::class;
    protected $viewPath = 'banned_words';

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
