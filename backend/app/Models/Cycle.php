<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area',
        'level',
    ];

    public function educationalCenters()
    {
        return $this->belongsToMany(EducationalCenter::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
