<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalCenter extends Model
{
    use HasFactory;

    public static $niveles_disponibles = [
        'PE' => 'Educación Primaria',
        'SE' => 'Educación Secundaria',
        'College' => 'Universidad',
        'FP' => 'Formación Profesional',
        'TM' => 'Administrador de TelamoNet',
        'US' => 'Usuario'
    ];

    public static function getEducationLevels(): array
    {
        return self::$niveles_disponibles;
    }

    protected $fillable = [
        'name',
        'type',
        'location',
        'cycles',
        'icon',
        'banner',
    ];

    protected $casts = [
    ];

    public function cycles()
    {
        return $this->belongsToMany(Cycle::class, 'educational_center_cycle');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function adminUser()
    {
        return $this->hasOne(User::class)->where('role', 'EI');
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'Student');
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', 'Teacher');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
