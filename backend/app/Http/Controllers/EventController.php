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
        $query = Event::with('educationalCenter')->withCount('participants');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%");
            });
        }

        if ($request->filled('center')) {
            $query->where('educational_center_id', $request->center);
        }

        if ($request->filled('role')) {
            $query->where('target_role', $request->role);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $events = $query->paginate(10);
        $roles_disponibles = Rol::all()->pluck('name', 'code')->toArray();
        $schools = EducationalCenter::pluck('name', 'id')->toArray();

        return view('users_events.index', compact('events', 'roles_disponibles', 'schools'));
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
                'start_time'            => 'required',
                'end_time'              => 'required',
                'educational_center_id' => 'required|exists:educational_centers,id',
                'target_role'           => 'nullable|string',
                'image'                 => 'nullable|image|max:51200' 
                
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
                $filename = 'event_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/events'), $filename);
                $event->image = '/uploads/events/' . $filename;
            }

            $event->save();
            $data['exito'] = 'Operación realizada correctamente';
        }

        return view('users_events.create', [
            'datos' => $data,
            'event' => $event,
            'fields' => $this->getEventFields($event),
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
            'fields' => $this->getEventFields($event),
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
                'start_time'            => 'required',
                'end_time'              => 'required',
                'educational_center_id' => 'required|exists:educational_centers,id',
                'target_role'           => 'nullable|string',
                'image'                 => 'nullable|image|max:51200'
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return view('users_events.create', [
                        'datos' => $datos,
                        'event' => $event,
                        'fields' => $this->getEventFields($event),
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
                $filename = 'event_' . $event->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/events'), $filename);
                $event->image = '/uploads/events/' . $filename;
            }

            $event->save();
            $datos['exito'] = 'Operación realizada correctamente';
            $disabled = 'disabled';

            if ($request->ajax()) {
                return view('users_events.create', [
                    'datos' => $datos,
                    'event' => $event,
                    'fields' => $this->getEventFields($event),
                    'disabled' => $disabled,
                    'oper' => 'edit' 
                ]); 
            }
        }

        return view('users_events.create', [
            'event' => $event,
            'datos' => $datos,
            'fields' => $this->getEventFields($event),
            'disabled' => $disabled,
            'oper' => 'edit'
        ]);
    }

    public function destroy(Request $request, $id='')
    {
        $event = Event::find($id);
        $datos = ['exito' => '']; 
        $disabled = 'disabled'; 

        if (!$event) {
            if ($request->ajax()) {
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
        }

        if ($request->isMethod('post')) {
            if ($event->image) {
                $publicPath = public_path(ltrim($event->image, '/'));
                if (file_exists($publicPath) && !is_dir($publicPath)) {
                    unlink($publicPath);
                }
                Storage::disk('public')->delete($event->image);
            }
            $event->delete();
            $datos['exito'] = 'Evento eliminado correctamente';
            if ($request->ajax()) {
                return view('users_events.create', [
                    'event' => $event,
                    'datos' => ['exito' => 'Evento eliminado correctamente'],
                    'fields' => $this->getEventFields($event),
                    'disabled' => 'disabled',
                    'oper' => 'destroy'
                ]);
            }

            return redirect()->route('admin.index');
        }

        $datos = ['exito' => ''];
        $disabled = 'disabled';
        return view('users_events.create', [
            'event' => $event,
            'datos' => $datos,
            'fields' => $this->getEventFields($event),
            'disabled' => $disabled,
            'oper' => 'destroy'
        ]);
    }

    /**
     * Define los campos para el formulario de eventos.
     */
    protected function getEventFields($event = null)
    {
        $schools = EducationalCenter::all()->pluck('name', 'id')->toArray();
        $roles = Rol::all();
        $roles_disponibles = Rol::all()->pluck('name', 'code')->toArray();

        $roleOptions = [];
        foreach($roles as $rolDb) {
            $roleValue = $rolDb->code ?? $rolDb->name;
            $roleOptions[$roleValue] = 'Solo ' . ($roles_disponibles[$rolDb->code] ?? $rolDb->name);
        }

        return [
            ['name' => 'title', 'label' => 'Nombre del Evento', 'placeholder' => 'Ej: Jornada...', 'value' => old('title', $event->title ?? ''), 'required' => true],
            ['name' => 'educational_center_id', 'type' => 'select', 'label' => 'Centro Organizador', 'options' => $schools, 'selectedValue' => old('educational_center_id', $event->educational_center_id ?? ''), 'placeholder' => 'Seleccionar centro...', 'required' => true],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Descripción', 'placeholder' => 'Detalles...', 'value' => old('description', $event->description ?? ''), 'rows' => 3, 'full' => true],
            ['name' => 'date', 'type' => 'date', 'label' => 'Fecha', 'value' => old('date', $event->date ?? ''), 'required' => true],
            ['name' => 'start_time', 'type' => 'time', 'label' => 'Hora Inicio', 'value' => old('start_time', $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : ''), 'required' => true],
            ['name' => 'end_time', 'type' => 'time', 'label' => 'Hora Fin', 'value' => old('end_time', $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : ''), 'required' => true],
            ['name' => 'location', 'label' => 'Lugar Exacto', 'placeholder' => 'Aula 104', 'value' => old('location', $event->location ?? '')],
            ['name' => 'target_role', 'type' => 'select', 'label' => 'Dirigido A', 'options' => $roleOptions, 'selectedValue' => old('target_role', $event->target_role ?? ''), 'placeholder' => 'Todos los roles pueden unirse'],
            ['name' => 'image', 'type' => 'file', 'label' => 'Imagen de Portada', 'full' => true]
        ];
    }

    /**
     * API methods
     */
    public function getEventsApi()
    {
        $events = Event::with('educationalCenter')->get();
        // Hide the heavy base64 'image' column to keep the JSON payload small
        return response()->json($events->makeHidden(['image']));
    }

    /**
     * Streams the event image directly.
     */
    public function streamImage($id)
    {
        $event = Event::findOrFail($id);
        
        if (!$event->image) {
            return response()->json(['error' => 'No image'], 404);
        }

        // 1. Handle new path-based storage (public/uploads/events/...)
        $publicPath = public_path(ltrim($event->image, '/'));
        if (file_exists($publicPath) && !is_dir($publicPath)) {
            return response()->file($publicPath, [
                'Cache-Control' => 'public, max-age=86400'
            ]);
        }

        // 2. Handle legacy Base64
        if (str_starts_with($event->image, 'data:')) {
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $event->image, $matches)) {
                $contentType = $matches[1];
                $data = base64_decode($matches[2]);
                
                return response($data)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=86400');
            }
        }

        // 3. Fallback for storage disk
        if (Storage::disk('public')->exists($event->image)) {
            $path = Storage::disk('public')->path($event->image);
            return response()->file($path, [
                'Cache-Control' => 'public, max-age=86400'
            ]);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    /**
     * Helper to resize images using GD.
     */
    private function resizeImage($path, $maxWidth)
    {
        list($width, $height, $type) = getimagesize($path);
        
        if ($width <= $maxWidth) {
            $newWidth = $width;
            $newHeight = $height;
        } else {
            $ratio = $width / $height;
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        }

        $src = null;
        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($path); break;
            case IMAGETYPE_PNG:  $src = imagecreatefrompng($path);  break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($path); break;
            default: return null;
        }

        if (!$src) return null;

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);

        return $dst;
    }
}
