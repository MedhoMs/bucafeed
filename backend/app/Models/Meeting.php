<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'teacher_name',
        'educational_center_id',
        'schedule',
        'description',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }
}
