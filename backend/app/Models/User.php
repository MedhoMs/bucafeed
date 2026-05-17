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

    protected $appends = ['role_name', 'is_verified', 'is_legal_tutor'];

    public function getIsLegalTutorAttribute(): bool
    {
        if ($this->role !== 'EU') {
            return false;
        }
        return $this->studentsOfTutor()->exists();
    }

    public function getRoleNameAttribute()
    {
        if ($this->role === 'EU' && $this->getIsLegalTutorAttribute()) {
            return 'Tutor Legal';
        }
        $roles = [
            'Admin' => 'Administrador',
            'EI' => 'Institución Educativa',
            'Teacher' => 'Profesor',
            'Student' => 'Alumno',
            'EU' => 'Usuario Externo'
        ];
        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Returns whether this student account has been verified by their educational center.
     * For non-student roles, always returns true (not subject to verification).
     * Returns true as fallback if the DB column doesn't exist yet (migration pending).
     */
    public function getIsVerifiedAttribute(): bool
    {
        if ($this->role !== 'Student') {
            return true;
        }
        try {
            // Use already loaded relation to avoid N+1
            if ($this->relationLoaded('student')) {
                return (bool) ($this->student?->verified ?? false);
            }
            return (bool) (Student::where('user_id', $this->id)->value('verified') ?? false);
        } catch (\Throwable $e) {
            // Column may not exist yet (migration pending) — treat as unverified but don't crash
            return false;
        }
    }

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

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function tutors()
    {
        return $this->belongsToMany(User::class, 'tutor_student', 'student_id', 'tutor_id')->withTimestamps();
    }

    public function studentsOfTutor()
    {
        return $this->belongsToMany(User::class, 'tutor_student', 'tutor_id', 'student_id')->withTimestamps();
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

