<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends TemplateController
{
    protected $model = Tag::class;
    protected $viewPath = 'tags';

    protected function getFormFields($tag = null)
    {
        return [
            ['name' => 'name', 'label' => 'Nombre del Tag', 'placeholder' => 'Ej: Matemáticas', 'value' => old('name', $tag->name ?? ''), 'required' => true, 'full' => true]
        ];
    }

    protected function rules($tag = null)
    {
        return [
            'name' => 'required|string|max:100|unique:tags,name,' . ($tag->id ?? 'NULL'),
        ];
    }
}





