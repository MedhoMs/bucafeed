<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CycleController extends TemplateController
{
    protected $model = Cycle::class;
    protected $viewPath = 'admin.global_cycles';

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        return $query;
    }

    protected function indexExtras(Request $request)
    {
        return [
            'levels' => Cycle::whereNotNull('level')->pluck('level', 'level')->unique()->toArray(),
            'total' => Cycle::count()
        ];
    }

    protected function getFormFields($cycle = null)
    {
        return [
            ['name' => 'name', 'label' => 'Nombre del Ciclo', 'placeholder' => 'Ej: 4º ESO', 'value' => old('name', $cycle->name ?? ''), 'required' => true],
            ['name' => 'level', 'type' => 'select', 'label' => 'Nivel Académico', 'options' => \App\Models\EducationalCenter::$niveles_disponibles, 'selectedValue' => old('level', $cycle->level ?? ''), 'required' => true],
            ['name' => 'area', 'label' => 'Área / Familia', 'placeholder' => 'Ej: Informática y Comunicaciones', 'value' => old('area', $cycle->area ?? '')]
        ];
    }

    protected function rules($cycle = null)
    {
        return [
            'name' => 'required|string|max:255|unique:cycles,name,' . ($cycle->id ?? 'NULL'),
            'level' => 'required|string'
        ];
    }
}
