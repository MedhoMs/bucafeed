<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EducationalCenter;
use App\Models\Rol;
use App\Models\Cycle;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EventController extends TemplateController
{
    protected $model = Event::class;
    protected $viewPath = 'users_events';
    protected $with = ['educationalCenter'];
    protected $withCount = ['participants'];
    protected $apiPerPage = 8;

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('center')) {
            $query->where('educational_center_id', $request->center);
        }
        if ($request->filled('center_id')) {
            $query->where('educational_center_id', $request->center_id);
        }
        if ($request->filled('role')) {
            $query->where('target_role', $request->role);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('participant_id')) {
            $query->whereHas('participants', function($q) use ($request) {
                $q->where('users.id', $request->participant_id);
            });
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
    public function generatePDF($id)
    {
        $event = Event::with('educationalCenter')->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.event_info', compact('event'));
        
        return $pdf->stream("Evento_{$event->id}.pdf");
    }

    public function streamImage($id)
    {
        $event = Event::findOrFail($id);
        return $this->streamFile($event->image);
    }

    /**
     * UNIRSE A EVENTO (API)
     */
    public function apiJoin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'No autenticado'], 401);

        $event = Event::findOrFail($id);
        
        // El usuario se une o se sale (toggle)
        $event->participants()->toggle($user->id);
        
        $joined = $event->participants()->where('user_id', $user->id)->exists();
        
        return response()->json([
            'joined' => $joined,
            'count' => $event->participants()->count(),
            'message' => $joined ? 'Te has unido al evento' : 'Has abandonado el evento'
        ]);
    }

    /**
     * API / Web Delete Event override
     */
    public function destroy(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($request->isMethod('get')) {
            return $this->renderForm($event, 'destroy');
        }

        $user = $request->user() ?: auth()->user();
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No autenticado'], 401);
            }
            return redirect()->back()->with('error', 'No autenticado');
        }

        $role = strtolower($user->role ?? '');
        $isSuperAdmin = in_array($role, ['admin', 'administrador', 'staff']);
        $isCenterAdmin = ($role === 'ei');

        if (!$isSuperAdmin && !$isCenterAdmin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tienes permisos para eliminar este evento.'], 403);
            }
            return redirect()->back()->with('error', 'No tienes permisos para eliminar este evento.');
        }

        if ($isCenterAdmin && $event->educational_center_id !== $user->educational_center_id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No puedes eliminar eventos de otros centros.'], 403);
            }
            return redirect()->back()->with('error', 'No puedes eliminar eventos de otros centros.');
        }

        $event->delete();

        if ($request->ajax()) {
            return $this->renderForm($event, 'destroy', '', 'Operación completada con éxito.');
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Evento eliminado correctamente.'
            ]);
        }

        return redirect()->route($this->viewPath . '.index')->with('success', 'Evento eliminado correctamente.');
    }
}
