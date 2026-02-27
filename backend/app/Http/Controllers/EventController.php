<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EducationalCenter;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with('educationalCenter')->withCount('participants')->get();
        $roles_disponibles = Rol::all()->mapWithKeys(function ($r) {
            return [$r->code ?? $r->name => $r->name];
        })->toArray();

        if ($request->ajax()) {
            return view('users_events.index', [
                'events' => $events,
                'roles_disponibles' => $roles_disponibles
            ])->renderSections()['content'];
        }
        
        return view('users_events.index', [
            'events' => $events,
            'roles_disponibles' => $roles_disponibles
        ]);
    }

    public function create(Request $request)
    {
        $data = ['exito' => ''];
        $event = new Event();

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'title'                 => 'required|string|max:255',
                'description'           => 'nullable|string',
                'location'              => 'nullable|string',
                'date'                  => 'required|date',
                'start_time'            => 'required|regex:/^\d{2}:\d{2}(:\d{2})?$/',
                'end_time'              => 'required|regex:/^\d{2}:\d{2}(:\d{2})?$/',
                'educational_center_id' => 'required|exists:educational_centers,id',
                'target_role'           => 'nullable|string',
                'image'                 => 'nullable|image|max:2048' // max 2MB
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('users_events.create', [
                        'datos' => $data,
                        'event' => $event,
                        'schools' => EducationalCenter::all(),
                        'roles' => Rol::all(),
                        'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
                        'disabled' => '',
                        'oper' => 'create'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $event->title = $request->input('title');
            $event->description = $request->input('description');
            $event->location = $request->input('location');
            $event->date = $request->input('date');
            $event->start_time = $request->input('start_time');
            $event->end_time = $request->input('end_time');
            $event->educational_center_id = $request->input('educational_center_id');
            $event->target_role = $request->input('target_role') ?: null;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $event->image = "data:image/{$extension};base64,{$base64}";
            }

            $event->save();
            $data['exito'] = 'Evento creado correctamente';
        }

        if ($request->ajax()) {
            return view('users_events.create', [
                'datos' => $data,
                'event' => $event,
                'schools' => EducationalCenter::all(),
                'roles' => Rol::all(),
                'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
                'disabled' => '',
                'oper' => 'create'
            ]);
        }

        return view('users_events.create', [
            'datos' => $data,
            'event' => $event,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
            'disabled' => '',
            'oper' => 'create'
        ]);
    }

    public function show($id)
    {
        $event = Event::with('participants', 'educationalCenter')->findOrFail($id);
        $datos = ['exito' => ''];

        return view('users_events.create',[
            'event' => $event,
            'datos' => $datos,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
            'disabled' => 'disabled',
            'oper' => 'show'
        ]);
    }

    public function edit(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $disabled = '';
        $datos['exito'] = '';

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'title'                 => 'required|string|max:255',
                'description'           => 'nullable|string',
                'location'              => 'nullable|string',
                'date'                  => 'required|date',
                'start_time'            => 'required|regex:/^\d{2}:\d{2}(:\d{2})?$/',
                'end_time'              => 'required|regex:/^\d{2}:\d{2}(:\d{2})?$/',
                'educational_center_id' => 'required|exists:educational_centers,id',
                'target_role'           => 'nullable|string',
                'image'                 => 'nullable|image|max:2048'
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('users_events.create', [
                        'datos' => $datos,
                        'event' => $event,
                        'schools' => EducationalCenter::all(),
                        'roles' => Rol::all(),
                        'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
                        'disabled' => $disabled,
                        'oper' => 'edit'
                    ])->withErrors($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $event->title = $request->input('title');
            $event->description = $request->input('description');
            $event->location = $request->input('location');
            $event->date = $request->input('date');
            $event->start_time = $request->input('start_time');
            $event->end_time = $request->input('end_time');
            $event->educational_center_id = $request->input('educational_center_id');
            $event->target_role = $request->input('target_role') ?: null;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $event->image = "data:image/{$extension};base64,{$base64}";
            }

            $event->save();
            $datos['exito'] = 'Evento actualizado correctamente';
            $disabled = 'disabled';

            if ($request->ajax()) {
                return view('users_events.create', [
                    'datos' => $datos,
                    'event' => $event,
                    'schools' => EducationalCenter::all(),
                    'roles' => Rol::all(),
                    'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
                    'disabled' => $disabled,
                    'oper' => 'edit' 
                ]); 
            }
        }

        return view('users_events.create', [
            'event' => $event,
            'datos' => $datos,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
            'disabled' => $disabled,
            'oper' => 'edit'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($request->isMethod('post')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $event->delete();
            $data = ['exito' => 'Evento eliminado correctamente'];

            if ($request->ajax()) {
                return view('users_events.create', [
                    'event' => $event,
                    'datos' => $data,
                    'schools' => EducationalCenter::all(),
                    'roles' => Rol::all(),
                    'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
                    'disabled' => 'disabled',
                    'oper' => 'destroy'
                ]);
            }
            return redirect()->route('admin.index');
        }

        return view('users_events.create', [
            'event' => $event,
            'datos' => ['exito' => ''],
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->mapWithKeys(fn ($r) => [$r->code ?? $r->name => $r->name])->toArray(),
            'disabled' => 'disabled',
            'oper' => 'destroy'
        ]);
    }

    /**
     * API methods
     */
    public function getEventsApi()
    {
        $events = Event::with('educationalCenter')->get();
        return response()->json($events);
    }
}
