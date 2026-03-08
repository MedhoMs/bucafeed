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
        $roles_disponibles = Rol::all()->pluck('name', 'code')->toArray();

        return view('users_events.index', compact('events', 'roles_disponibles'));
    }

    public function create(Request $request)
    {
        $data = ['exito' => ''];
        $event = new Event();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
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
            $data['exito'] = 'Operación realizada correctamente';
        }

        if ($request->ajax()) {
            return view('users_events.create', [
                'datos' => $data,
                'event' => $event,
                'schools' => EducationalCenter::all(),
                'roles' => Rol::all(),
                'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
                'disabled' => '',
                'oper' => 'create'
            ]);
        }

        $event = new Event();

        return view('users_events.create', [
            'datos' => $data,
            'event' => $event,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'disabled' => '',
            'oper' => 'create'
        ]);
    }

    public function show($id)
    {
        $event = Event::with('participants', 'educationalCenter')->find($id);
        $datos = ['exito' => ''];

        return view('users_events.create',[
            'event' => $event,
            'datos' => $datos,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'disabled' => 'disabled',
            'oper' => 'show'
        ]);
    }

    public function edit(Request $request, $id)
    {
        $event = Event::find($id);
        $disabled = '';
        $datos['exito'] = '';

        if ($request->isMethod('post')) {
            $validated = $request->validate([
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
            $datos['exito'] = 'Operación realizada correctamente';
            $disabled = 'disabled';

            if ($request->ajax()) {
                return view('users_events.create', [
                    'datos' => $datos,
                    'event' => $event,
                    'schools' => EducationalCenter::all(),
                    'roles' => Rol::all(),
                    'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
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
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'disabled' => $disabled,
            'oper' => 'edit'
        ]);
    }

    public function destroy(Request $request, $id='')
    {
        $event = Event::find($id);

        if (!$event) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Evento no encontrado'], 404);
            }
        }

        if ($request->isMethod('post')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $event->delete();

            if ($request->ajax()) {
                return response()->json([
                    'exito' => 'Evento eliminado correctamente'
                ]);
            }

            return redirect()->route('admin.index');
        }

        $datos = ['exito' => ''];
        $disabled = 'disabled';
        return view('users_events.create', [
            'event' => $event,
            'datos' => $datos,
            'schools' => EducationalCenter::all(),
            'roles' => Rol::all(),
            'roles_disponibles' => Rol::all()->pluck('name', 'code')->toArray(),
            'disabled' => $disabled,
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
