<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_required', // puntos_necesarios
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievement')
                    ->withPivot('awarded_at')
                    ->withTimestamps();
    }
}



