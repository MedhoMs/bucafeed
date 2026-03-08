<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $appends = ['image_url'];

    protected $fillable = [
        'educational_center_id',
        'title',
        'description',
        'image',
        'location',
        'date',
        'start_time',
        'end_time',
        'target_role'
    ];

    public function educationalCenter()
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants');
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

                // For everything else (Base64 or internal /uploads paths), 
                // use the streaming API route for consistency and performance.
                // We add a timestamp as a query parameter to avoid browser caching issues after updates.
                return route('api.event.image', ['id' => $this->id, 't' => $this->updated_at?->timestamp]);
            },
        );
    }
}




