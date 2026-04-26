<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Http\Request;

class QuestionController extends TemplateController
{
    protected $model = Question::class;
    protected $viewPath = 'questions';
    protected $with = ['user', 'tags', 'answers.user'];

    public function destroy(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        
        if ($request->isMethod('get')) {
            return $this->renderForm($question, 'destroy');
        }

        // 1. Eliminar respuestas vinculadas
        $question->answers()->delete();
        
        // 2. Desvincular etiquetas
        $question->tags()->detach();
        
        // 3. Eliminar la pregunta
        $question->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pregunta eliminada correctamente.']);
        }
        return redirect()->route('question.index')->with('success', 'Pregunta eliminada correctamente.');
    }

    protected function extraFilters($query, Request $request)
    {
        if ($request->filled('tag')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }
        return $query;
    }

    protected function indexExtras(Request $request)
    {
        return [
            'tags_disponibles' => Tag::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }

    protected function getFormFields($question = null)
    {
        $users = User::orderBy('name')->get()->mapWithKeys(function($u) {
            return [$u->id => $u->name . ' ' . $u->last_name . ' (' . $u->email . ')'];
        })->toArray();
        
        $tags = [];
        if ($question && $question->user_id) {
            $user = User::with(['groupsAsStudent.cycle.tags'])->find($question->user_id);
            if ($user && $user->groupsAsStudent) {
                $userTags = $user->groupsAsStudent->filter(fn($g) => $g->cycle != null)
                                ->flatMap(fn($g) => $g->cycle->tags);
                $tags = $userTags->unique('id')->sortBy('name')->pluck('name', 'id')->toArray();
            }
            if (empty($tags)) {
                $tags = Tag::orderBy('name')->pluck('name', 'id')->toArray();
            }
        } else {
            $tags = Tag::orderBy('name')->pluck('name', 'id')->toArray();
        }

        return [
            ['name' => 'user_id', 'type' => 'select', 'label' => 'Autor de la Pregunta', 'options' => $users, 'placeholder' => 'Selecciona al autor...', 'required' => true, 'selectedValue' => old('user_id', $question->user_id ?? '')],
            ['name' => 'title', 'label' => 'Título descriptivo', 'placeholder' => 'Ej: ¿Cómo crear un componente en Vue?', 'required' => true, 'value' => old('title', $question->title ?? '')],
            ['name' => 'content', 'type' => 'textarea', 'label' => 'Contenido / Cuerpo', 'placeholder' => 'Describe tu duda con detalle...', 'required' => true, 'full' => true, 'value' => old('content', $question->content ?? '')],
            ['name' => 'image', 'type' => 'file', 'label' => 'Imagen de Apoyo', 'full' => true, 'previewUrl' => $question->image ?? null],
            ['name' => 'tags', 'type' => 'checkbox-group', 'label' => 'Temas / Etiquetas', 'options' => $tags, 'full' => true, 'selectedValues' => old('tags', $question ? $question->tags->pluck('id')->toArray() : [])]
        ];
    }

    protected function rules($question = null)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:4096',
            'tags' => 'nullable|array',
        ];
    }

    /**
     * Sobrescribimos SAVE para manejar la sincronización de etiquetas
     */
    protected function save(Request $request, $question = null)
    {
        $response = parent::save($request, $question);
        
        $savedId = session('saved_model_id');
        $obj = $question ?? ($savedId ? Question::find($savedId) : Question::latest()->first());
        
        if ($obj && $request->has('tags')) {
            $obj->tags()->sync($request->input('tags'));
        }

        return $response;
    }

    public function getTagsByUser($userId)
    {
        $user = User::with(['groupsAsStudent.cycle.tags'])->find($userId);
        if (!$user) return response()->json([]);
        
        $tags = collect();

        if ($user->groupsAsStudent && $user->groupsAsStudent->isNotEmpty()) {
            $tags = $user->groupsAsStudent->filter(function($g) {
                return $g->cycle != null;
            })->flatMap(function($g) {
                return $g->cycle->tags;
            });
        }
        
        return response()->json($tags->unique('id')->pluck('name', 'id'));
    }
}
