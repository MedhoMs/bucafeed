<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'educational_center_id',
        'course',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }
}




