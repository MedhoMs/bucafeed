<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\EducationalCenter;
use Illuminate\Http\Request;

class PublicationController extends TemplateController
{
    protected $model = Publication::class;
    protected $viewPath = 'publications';
    protected $with = ['educationalCenter'];

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('center')) {
            $query->where('educational_center_id', $request->center);
        }
        if ($request->filled('center_id')) {
            $query->where('educational_center_id', $request->center_id);
        }
        return $query;
    }

    protected function getFormFields($publication = null)
    {
        try {
            $schools = EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray();
            
            return [
                ['name' => 'title', 'label' => 'Título de la Publicación', 'placeholder' => 'Ej: Logro académico', 'value' => old('title', $publication->title ?? ''), 'required' => true],
                ['name' => 'educational_center_id', 'type' => 'select', 'label' => 'Centro Publicador', 'options' => $schools, 'selectedValue' => old('educational_center_id', $publication->educational_center_id ?? ''), 'placeholder' => 'Seleccionar centro...', 'required' => true],
                ['name' => 'description', 'type' => 'textarea', 'label' => 'Contenido', 'placeholder' => 'Contenido de la publicación...', 'value' => old('description', $publication->description ?? ''), 'rows' => 5, 'full' => true],
                ['name' => 'image', 'type' => 'file', 'label' => 'Imagen', 'full' => true, 'previewUrl' => $publication->image ?? null]
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in PublicationController@getFormFields: " . $e->getMessage());
            return [];
        }
    }

    protected function rules($publication = null)
    {
        return [
            'title'                 => 'required|string|max:255',
            'educational_center_id' => 'required|exists:educational_centers,id',
            'description'           => 'required|string',
            'image'                 => 'nullable|image|max:2048',
        ];
    }

    public function streamImage($id)
    {
        $publication = Publication::findOrFail($id);
        return $this->streamFile($publication->image);
    }
}
