<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_center_id',
        'cycle_id',
        'name',
        'tutor_id',
    ];

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_student', 'group_id', 'user_id');
    }

    public function subjectsWithTeachers()
    {
        return $this->belongsToMany(Tag::class, 'group_tag_teacher', 'group_id', 'tag_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }
}
