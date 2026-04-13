<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * CONTROLADOR MAESTRO DE TELAMONET (PLANTILLA)
 * Gestiona automáticamente el 90% de la lógica CRUD.
 * Los controladores hijos deben heredar de esta clase.
 */
abstract class TemplateController extends Controller
{
    protected $model;      // El Modelo (ej: User::class)
    protected $viewPath;   // El path de las vistas (ej: 'users')
    protected $with = [];  // Relaciones a cargar (ej: ['student', 'teacher'])
    protected $withCount = []; // Conteos de relaciones (ej: ['students', 'teachers'])

    /**
     * Endpoint API genérico para el Frontend
     */
    public function apiIndex(Request $request)
    {
        $query = $this->model::query();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        // Aplicar filtros extra si existen
        $query = $this->extraFilters($query, $request);

        return response()->json($query->orderBy('id', 'desc')->get());
    }

    /**
     * Helper genérico para servir imágenes
     */
    protected function streamFile($content)
    {
        if (!$content) abort(404);

        if (str_starts_with($content, 'data:image')) {
            $data = explode(',', $content);
            return response(base64_decode($data[1]))->header('Content-Type', 'image/png');
        }

        $cleanPath = ltrim($content, '/');
        return response()->file(storage_path('app/public/' . $cleanPath));
    }

    /**
     * LISTADO AUTOMÁTICO CON FILTROS
     */
    public function index(Request $request)
    {
        $query = $this->model::with($this->with);

        // Búsqueda inteligente
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $columns = (new $this->model)->getFillable();
                foreach($columns as $col) {
                    $q->orWhere($col, 'like', "%$search%");
                }
            });
        }

        // Permitir que el hijo añade filtros extra
        $query = $this->extraFilters($query, $request);

        $items = $query->orderBy('id', 'desc')->paginate(10);

        // Solo devolver JSON si estamos en una ruta de API o se pide explícitamente JSON
        if ($request->is('api/*') || ($request->expectsJson() && !$request->ajax())) {
            return response()->json($items);
        }

        // Nombres automáticos unificados 
        $baseName = class_basename($this->model);
        $pluralName = Str::snake(Str::plural($baseName));
        
        $data = [
            'models' => $items,
            $pluralName => $items,
        ];

        // Atajos para compatibilidad con vistas actuales
        if ($baseName === 'EducationalCenter') $data['centers'] = $items;
        if ($baseName === 'Event') $data['events'] = $items;

        return view($this->viewPath . '.index', array_merge($data, $this->indexExtras($request)));
    }

    /**
     * CREATE / EDIT / SHOW UNIFICADOS
     */
    public function create(Request $request)
    {
        $model = new $this->model;
        return $this->renderForm($model, 'create');
    }

    public function show($id)
    {
        $model = $this->model::with($this->with)->findOrFail($id);
        return $this->renderForm($model, 'show', 'disabled');
    }

    public function edit($id)
    {
        $model = $this->model::findOrFail($id);
        return $this->renderForm($model, 'edit');
    }

    protected function renderForm($model, $oper, $disabled = '', $success = '')
    {
        $baseName = class_basename($this->model);
        $data = [
            'model' => $model,
            strtolower($baseName) => $model,
            'fields' => $this->getFormFields($model),
            'oper' => $oper,
            'disabled' => $disabled,
            'datos' => ['exito' => $success ?: session('success', '')]
        ];

        // Atajos automáticos y específicos para compatibilidad
        $data[Str::snake($baseName)] = $model;
        
        if ($baseName === 'EducationalCenter') $data['center'] = $model;

        return view($this->viewPath . '.create', $data);
    }

    /**
     * STORE / UPDATE AUTOMÁTICO
     */
    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, $id)
    {
        $model = $this->model::findOrFail($id);
        return $this->save($request, $model);
    }

    protected function save(Request $request, $model = null)
    {
        $isEdit = (bool)$model;
        $validator = Validator::make($request->all(), $this->rules($model));

        if ($validator->fails()) {
            if ($request->ajax()) {
                return $this->renderForm($model ?? new $this->model, $isEdit ? 'edit' : 'create')->withErrors($validator);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Manejo automático de archivos
        $fileFields = ['image', 'icon', 'banner', 'profile_picture'];
        foreach($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/' . strtolower(class_basename($this->model));
                if ($field === 'profile_picture') $path = 'uploads/profiles';
                
                $file->storeAs('public/' . $path, $filename);
                $data[$field] = $path . '/' . $filename;
            }
        }

        if ($isEdit) {
            $model->update($data);
        } else {
            $model = $this->model::create($data);
        }

        if ($request->ajax()) {
            return $this->renderForm($model, $isEdit ? 'edit' : 'create', '', 'Operación completada con éxito.');
        }

        return redirect()->route($this->viewPath . '.index')->with('success', 'Guardado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->model::findOrFail($id);
        $model->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route($this->viewPath . '.index');
    }

    // Métodos a desarrollar por el controlador hijo
    abstract protected function getFormFields($model = null);
    abstract protected function rules($model = null);
    
    protected function extraFilters($query, Request $request) { return $query; }
    protected function indexExtras(Request $request) { return []; }
}
