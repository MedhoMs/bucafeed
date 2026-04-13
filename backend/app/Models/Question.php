<?php

namespace App\Models;

/**
 * MODELO DE PREGUNTAS / FORO
 */
class Question extends TemplateModel
{
    /**
     * Campos asignables.
     */
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'image',
    ];

    /**
     * RELACIONES
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'question_tag');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Relación opcional con validaciones de IA
     */
    public function aiValidations()
    {
        return $this->hasOne(AiValidation::class);
    }
}
