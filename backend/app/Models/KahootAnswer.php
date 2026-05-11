<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KahootAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'question_index',
        'selected_answer',
        'is_correct',
        'score',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(KahootSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
