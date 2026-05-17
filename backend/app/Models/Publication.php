<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Publication extends TemplateModel
{
    /**
     * Campos asignables.
     */
    protected $fillable = [
        'title',
        'description',
        'educational_center_id',
        'image',
    ];

    protected $appends = ['image_url', 'center_name'];
    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    /**
     * Nombre del centro para la API.
     */
    protected function centerName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->educationalCenter?->name ?? 'N/A'
        );
    }

    /**
     * Get the URL for the publication image.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->image) return null;
                
                // If it's a full URL (external), return it directly
                if (str_starts_with($this->image, 'http')) return $this->image;

                // Si está en public/ (empieza por /uploads o uploads)
                if (str_starts_with($this->image, '/uploads') || str_starts_with($this->image, 'uploads')) {
                    return asset($this->image);
                }

                // For Base64 or old paths
                return route('api.publication.image', ['id' => $this->id, 't' => $this->updated_at?->timestamp]);
            },
        );
    }
}
