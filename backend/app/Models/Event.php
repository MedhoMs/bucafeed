<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Event extends TemplateModel
{
    /**
     * Campos asignables.
     */
    protected $fillable = [
        'title',
        'description',
        'location',
        'date',
        'start_time',
        'end_time',
        'educational_center_id',
        'target_role',
        'image',
        'is_kahoot',
        'kahoot_questions',
    ];

    protected $casts = [
        'is_kahoot'        => 'boolean',
        'kahoot_questions' => 'array',
    ];

    /**
     * RELACIONES
     */

    /**
     * Campos que se incluyen siempre en JSON
     */
    protected $appends = ['image_url', 'center_name', 'participants_count'];

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants')
                    ->withTimestamps();
    }

    /**
     * Nombre del centro para la API
     */
    protected function centerName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->educationalCenter?->name ?? 'N/A'
        );
    }

    /**
     * Get the URL for the event image.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->image) return null;
                
                // If it's a full URL (external), return it directly
                if (str_starts_with($this->image, 'http')) return $this->image;

                // Si está en public/ (empieza por /uploads)
                if (str_starts_with($this->image, '/uploads') || str_starts_with($this->image, 'uploads')) {
                    return asset($this->image);
                }

                // For Base64 or old paths
                return route('api.event.image', ['id' => $this->id, 't' => $this->updated_at?->timestamp]);
            },
        );
    }
    /**
     * Conteo de participantes para la API
     */
    protected function participantsCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participants()->count()
        );
    }
}





