<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KahootSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'teacher_id',
        'title',
        'questions',
        'status',
        'current_question_index',
        'time_per_question',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function answers()
    {
        return $this->hasMany(KahootAnswer::class, 'session_id');
    }
}
