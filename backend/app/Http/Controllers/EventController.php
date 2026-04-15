<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EducationalCenter;
use App\Models\Rol;
use App\Models\Cycle;
use Illuminate\Http\Request;

class EventController extends TemplateController
{
    protected $model = Event::class;
    protected $viewPath = 'users_events';
    protected $with = ['educationalCenter'];

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('center')) {
            $query->where('educational_center_id', $request->center);
        }
        if ($request->filled('role')) {
            $query->where('target_role', $request->role);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        return $query;
    }

    protected function indexExtras(Request $request)
    {
        try {
            return [
                'roles_disponibles' => Rol::pluck('name', 'code')->toArray(),
                'centros' => EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray(),
                'schools' => EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray(),
                'niveles_disponibles' => EducationalCenter::$niveles_disponibles,
                'ciclos_disponibles' => Cycle::orderBy('name')->pluck('name', 'id')->toArray()
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in EventController@indexExtras: " . $e->getMessage());
            return [
                'roles_disponibles' => [],
                'centros' => [],
                'schools' => [],
                'niveles_disponibles' => [],
                'ciclos_disponibles' => []
            ];
        }
    }

    protected function getFormFields($event = null)
    {
        try {
            $schools = EducationalCenter::orderBy('name')->pluck('name', 'id')->toArray();
            $roles = Rol::pluck('name', 'code')->toArray();
            
            $roleOptions = [];
            foreach($roles as $code => $name) {
                $roleOptions[$code] = 'Solo ' . $name;
            }

            return [
                ['name' => 'title', 'label' => 'Nombre del Evento', 'placeholder' => 'Ej: Jornada de Puertas Abiertas', 'value' => old('title', $event->title ?? ''), 'required' => true],
                ['name' => 'educational_center_id', 'type' => 'select', 'label' => 'Centro Organizador', 'options' => $schools, 'selectedValue' => old('educational_center_id', $event->educational_center_id ?? ''), 'placeholder' => 'Seleccionar centro...', 'required' => true],
                ['name' => 'description', 'type' => 'textarea', 'label' => 'Descripción', 'placeholder' => 'Detalles del evento...', 'value' => old('description', $event->description ?? ''), 'rows' => 3, 'full' => true],
                ['name' => 'date', 'type' => 'date', 'label' => 'Fecha', 'value' => old('date', $event->date ?? ''), 'required' => true],
                ['name' => 'start_time', 'type' => 'time', 'label' => 'Hora Inicio', 'value' => old('start_time', $event->start_time ? substr($event->start_time, 0, 5) : ''), 'required' => true],
                ['name' => 'end_time', 'type' => 'time', 'label' => 'Hora Fin', 'value' => old('end_time', $event->end_time ? substr($event->end_time, 0, 5) : ''), 'required' => true],
                ['name' => 'location', 'label' => 'Lugar Exacto', 'placeholder' => 'Ej: Aula de Informática', 'value' => old('location', $event->location ?? '')],
                ['name' => 'target_role', 'type' => 'select', 'label' => 'Dirigido A', 'options' => $roleOptions, 'selectedValue' => old('target_role', $event->target_role ?? ''), 'placeholder' => 'Todos los roles pueden unirse'],
                ['name' => 'image', 'type' => 'file', 'label' => 'Imagen de Portada', 'full' => true, 'previewUrl' => $event->image ?? null]
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in EventController@getFormFields: " . $e->getMessage());
            return [];
        }
    }

    protected function rules($event = null)
    {
        return [
            'title'                 => 'required|string|max:255',
            'educational_center_id' => 'required|exists:educational_centers,id',
            'date'                  => 'required|date',
            'start_time'            => 'required',
            'end_time'              => 'required',
            'image'                 => 'nullable|image|max:2048',
            'is_kahoot'             => 'sometimes|boolean',
            'kahoot_questions'      => 'sometimes|nullable|string|required_if:is_kahoot,1',
        ];
    }

    protected function save(Request $request, $model = null)
    {
        if ($request->boolean('is_kahoot')) {
            $request->merge([
                'title' => $request->input('kahoot_title'),
                'description' => $request->input('kahoot_description'),
                'educational_center_id' => $request->input('kahoot_educational_center_id'),
                'date' => $request->input('kahoot_date'),
                'start_time' => $request->input('kahoot_start_time'),
                'end_time' => $request->input('kahoot_end_time'),
                'location' => $request->input('kahoot_location'),
                'target_role' => $request->filled('kahoot_target_role') ? $request->input('kahoot_target_role') : null,
            ]);
        }

        return parent::save($request, $model);
    }

    /**
     * API / Public methods
     */
    public function streamImage($id)
    {
        $event = Event::findOrFail($id);
        return $this->streamFile($event->image);
    }
}
