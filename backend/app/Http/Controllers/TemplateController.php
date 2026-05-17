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

    protected $apiPerPage = 12; // Número de elementos por página en la API

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

        // Devolvemos paginado
        return response()->json($query->orderBy('id', 'desc')->paginate($this->apiPerPage));
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

        return response()->file(storage_path('app/public/' . $content));
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
        $pluralName = Str::camel(Str::plural($baseName));
        
        $data = [
            'models' => $items,
            $pluralName => $items,
        ];

        // Atajos para compatibilidad con nombres irregulares
        if ($baseName === 'EducationalCenter') $data['centers'] = $items;
        if ($baseName === 'Rol') $data['roles'] = $items;

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

    public function show(Request $request, $id)
    {
        $model = $this->model::with($this->with)->findOrFail($id);

        if ($request->is('api/*') || ($request->expectsJson() && !$request->ajax())) {
            return response()->json($model);
        }

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
        $singularName = Str::camel($baseName);

        $data = [
            'model' => $model,
            $singularName => $model,
            'fields' => $this->getFormFields($model),
            'oper' => $oper,
            'disabled' => ($oper === 'show' || $oper === 'destroy') ? 'disabled' : $disabled,
            'datos' => ['exito' => $success ?: session('success', '')]
        ];
        
        if ($baseName === 'EducationalCenter') $data['center'] = $model;
        if ($baseName === 'Rol') $data['role'] = $model;

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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Los datos proporcionados no son válidos.',
                    'errors' => $validator->errors()
                ], 422);
            }
            if ($request->ajax()) {
                return $this->renderForm($model ?? new $this->model, $isEdit ? 'edit' : 'create')->withErrors($validator);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Manejo automático de archivos (Mejorado)
        $fileFields = ['image', 'icon', 'banner', 'profile_picture'];
        foreach($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                
                // Determinar carpeta basada en el modelo (Estandarizado)
                $modelName = class_basename($this->model);
                $folder = Str::snake(Str::plural($modelName));
                
                if ($field === 'profile_picture') $folder = 'profiles';
                
                $path = 'uploads/' . $folder;
                $fullPath = public_path($path);
                
                if (!file_exists($fullPath)) {
                    @mkdir($fullPath, 0777, true);
                }
                
                if ($file->move($fullPath, $filename)) {
                    $data[$field] = '/' . $path . '/' . $filename;
                }
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

        if ($request->expectsJson()) {
            return $model;
        }

        session()->flash('saved_model_id', $model->id);
        return redirect()->route($this->viewPath . '.index')->with('success', 'Guardado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->model::findOrFail($id);

        if ($request->isMethod('get')) {
            return $this->renderForm($model, 'destroy');
        }

        $model->delete();

        if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'message' => 'Eliminado correctamente']);
        }
        return redirect()->route($this->viewPath . '.index')->with('success', 'Eliminado correctamente.');
    }

    // Métodos a desarrollar por el controlador hijo
    abstract protected function getFormFields($model = null);
    abstract protected function rules($model = null);
    
    protected function extraFilters($query, Request $request) { return $query; }
    protected function indexExtras(Request $request) { return []; }
}
