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
                'image'                 => 'nullable|image|max:51200' // max 50MB
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
                $extension = strtolower($file->getClientOriginalExtension());
                $tempPath = $file->getRealPath();
                $resizedImage = $this->resizeImage($tempPath, 800);
                
                if ($resizedImage) {
                    ob_start();
                    if (in_array($extension, ['jpg', 'jpeg'])) imagejpeg($resizedImage, null, 80);
                    elseif ($extension == 'png') imagepng($resizedImage, null, 8);
                    elseif ($extension == 'webp') imagewebp($resizedImage, null, 80);
                    else imagejpeg($resizedImage, null, 80);
                    
                    $imageData = ob_get_clean();
                    $base64 = base64_encode($imageData);
                    $event->image = "data:image/{$extension};base64,{$base64}";
                    imagedestroy($resizedImage);
                } else {
                    $base64 = base64_encode(file_get_contents($tempPath));
                    $event->image = "data:image/{$extension};base64,{$base64}";
                }
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
                $extension = strtolower($file->getClientOriginalExtension());
                $tempPath = $file->getRealPath();
                $resizedImage = $this->resizeImage($tempPath, 800);
                
                if ($resizedImage) {
                    ob_start();
                    if (in_array($extension, ['jpg', 'jpeg'])) imagejpeg($resizedImage, null, 80);
                    elseif ($extension == 'png') imagepng($resizedImage, null, 8);
                    elseif ($extension == 'webp') imagewebp($resizedImage, null, 80);
                    else imagejpeg($resizedImage, null, 80);
                    
                    $imageData = ob_get_clean();
                    $base64 = base64_encode($imageData);
                    $event->image = "data:image/{$extension};base64,{$base64}";
                    imagedestroy($resizedImage);
                } else {
                    $base64 = base64_encode(file_get_contents($tempPath));
                    $event->image = "data:image/{$extension};base64,{$base64}";
                }
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
            if ($event->image && !str_starts_with($event->image, 'data:')) {
                Storage::disk('public')->delete($event->image);
            }
            $event->delete();
            $datos['exito'] = 'Evento eliminado correctamente';
            if ($request->ajax()) {
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

        // If it's a full URL, we could redirect or proxy, but usually it's base64 or a path
        if (str_starts_with($event->image, 'data:')) {
            // Extract the metadata and data from the base64 string
            // Format: data:image/png;base64,iVBOR...
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $event->image, $matches)) {
                $contentType = $matches[1];
                $data = base64_decode($matches[2]);
                
                return response($data)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
            }
        }

        // If it's a path in storage
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
