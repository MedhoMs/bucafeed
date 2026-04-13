<?php

namespace App\Models;

/**
 * MODELO DE CENTROS EDUCATIVOS
 */
class EducationalCenter extends TemplateModel
{
    /**
     * Campos asignables.
     */
    protected $fillable = [
        'name',
        'location',
        'type',
        'icon',
        'banner',
        'category',
        'admin_user_id',
    ];

    /**
     * Propiedades estáticas de utilidad.
     */
    public static $niveles_disponibles = [
        'PE' => 'Primaria',
        'SE' => 'Secundaria',
        'HE' => 'FP Superior',
        'FP' => 'Formación Profesional',
        'UR' => 'Universidad',
        'TM' => 'Administrador',
        'US' => 'Usuario Externo',
        'EI' => 'Centro Educativo'
    ];

    /**
     * RELACIONES
     */

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', 'Teacher');
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'Student');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function cycles()
    {
        return $this->belongsToMany(Cycle::class, 'educational_center_cycle', 'educational_center_id', 'cycle_id');
    }

    public static function getEducationLevels(): array
    {
        return self::$niveles_disponibles;
    }
}