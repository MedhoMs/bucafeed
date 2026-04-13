<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role',
        'reputation',
        'educational_center_id',
        'dni',
        'education_level',
        'institution_name',
        'profile_picture',
        'banner',
        'description',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'student_teacher', 'student_id', 'teacher_id');
    }

    public function assignedStudents()
    {
        return $this->belongsToMany(User::class, 'student_teacher', 'teacher_id', 'student_id');
    }

    public function groupsAsStudent()
    {
        return $this->belongsToMany(Group::class, 'group_student', 'user_id', 'group_id');
    }

    public function groupsAsTeacher()
    {
        return $this->belongsToMany(Group::class, 'group_tag_teacher', 'user_id', 'group_id')
                    ->withPivot('tag_id')
                    ->distinct();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}





