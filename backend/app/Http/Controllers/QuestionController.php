<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        // Si la ruta empieza por /api, devolvemos JSON
        if ($request->is('api*')) {
            $query = Question::with(['user', 'tags']);

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if ($request->has('tags')) {
                $tags = $request->input('tags');
                if (is_string($tags)) {
                    $tags = explode(',', $tags);
                }
                $query->whereHas('tags', function($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            }

            return $query->latest()->get();
        }

        // Lógica para el Panel de Administración (Blade) - Se ejecuta para /admin/* incluso si es AJAX
        $query = Question::with(['user', 'tags'])->withCount('answers');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }

        $questions = $query->paginate(10);
        $tags_disponibles = \App\Models\Tag::pluck('name', 'id')->toArray();

        return view('questions.index', compact('questions', 'tags_disponibles'));
    }

    public function create(Request $request)
    {
        $question = new Question();
        $users = \App\Models\User::orderBy('name')->get()->mapWithKeys(function($u) {
            return [$u->id => $u->name . ' ' . $u->last_name . ' (' . $u->email . ')'];
        })->toArray();
        $tags = \App\Models\Tag::pluck('name', 'id')->toArray();

        $fields = [
            ['name' => 'user_id', 'type' => 'select', 'label' => 'Autor', 'options' => $users, 'placeholder' => 'Selecciona al alumno...', 'required' => true],
            ['name' => 'title', 'label' => 'Título de la Pregunta', 'placeholder' => 'Ej: ¿Cómo despejar X en una ecuación?', 'required' => true],
            ['name' => 'content', 'type' => 'textarea', 'label' => 'Contenido / Detalle', 'placeholder' => 'Explica detalladamente la duda...', 'required' => true, 'full' => true],
            ['name' => 'image', 'type' => 'file', 'label' => 'Imagen de Apoyo (Opcional)', 'full' => true],
            ['name' => 'tags', 'type' => 'checkbox-group', 'label' => 'Etiquetas (Materias)', 'options' => $tags, 'full' => true]
        ];

        return view('questions.create', [
            'question' => $question,
            'fields' => $fields,
            'oper' => 'create',
            'datos' => ['exito' => ''],
            'disabled' => ''
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:4096',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_q_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/questions'), $filename);
            $validated['image'] = '/uploads/questions/' . $filename;
        }

        $question = Question::create($validated);
        
        if (isset($validated['tags'])) {
            $question->tags()->attach($validated['tags']);
        }

        if ($request->is('api*')) {
            return $question->load('tags');
        }

        return $this->create($request)->with('success', 'Pregunta creada correctamente');
    }

    public function getTagsByUser($userId)
    {
        $user = \App\Models\User::with('student.cycle.tags')->find($userId);
        
        if ($user && $user->student && $user->student->cycle) {
            $tags = $user->student->cycle->tags->pluck('name', 'id');
        } else {
            $tags = \App\Models\Tag::pluck('name', 'id');
        }

        return response()->json($tags);
    }

    public function show($id)
    {
        if (is_numeric($id)) {
            $question = Question::with(['user', 'tags', 'answers.user', 'aiValidations'])->findOrFail($id);
        } else {
            $question = $id;
        }

        if (request()->is('api*')) {
            $answers = $question->answers()
                ->join('users', 'answers.user_id', '=', 'users.id')
                ->select('answers.*')
                ->orderByDesc('answers.is_useful')
                ->orderByDesc('users.reputation')
                ->orderByDesc('answers.reputation')
                ->with('user')
                ->get();
            $question->setRelation('answers', $answers);
            return $question;
        }

        return view('questions.show', compact('question'));
    }

    public function destroy(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        if ($request->is('api*')) {
            return response()->noContent();
        }

        return redirect()->route('question.index')->with('success', 'Pregunta eliminada');
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'title' => 'string',
            'content' => 'string',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $question->update($validated);

        if (isset($validated['tags'])) {
            $question->tags()->sync($validated['tags']);
        }

        return $question;
    }
}
