<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'result',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}




