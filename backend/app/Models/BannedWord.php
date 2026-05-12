<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BannedWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'detected_at',
        'context',
    ];

    /**
     * Invalidar la caché cada vez que se modifique la lista.
     */
    protected static function booted()
    {
        static::saved(fn () => Cache::forget('banned_words'));
        static::deleted(fn () => Cache::forget('banned_words'));
    }
}









