<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacialVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_hash',
        'is_validated',
        'verified_at',
    ];

    protected $casts = [
        'is_validated' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}





