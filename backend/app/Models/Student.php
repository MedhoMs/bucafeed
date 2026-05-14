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
        'cycle_id',
        'course',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }
}





