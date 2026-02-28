<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    
    protected $fillable = [
        'name',
        'code',
        'guard_name'
    ];

    protected $attributes = [
        'guard_name' => 'web'
    ];

    public static $roles_disponibles = [
        'EU' => 'Usuario externo',
        'Student' => 'Estudiante',
        'Teacher' => 'Profesor',
        'Admin' => 'Administrador',
        'EI' => 'Centro educativo'
    ];
}
