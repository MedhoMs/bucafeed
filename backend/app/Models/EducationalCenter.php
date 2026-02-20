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
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
