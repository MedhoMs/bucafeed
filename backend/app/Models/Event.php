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
                
                // If the app is being fetched via API or we want to optimize, 
                // we return a permanent URL to the streaming endpoint.
                if (str_starts_with($this->image, 'data:')) {
                    return route('api.event.image', ['id' => $this->id]);
                }
                
                if (str_starts_with($this->image, 'http')) return $this->image;
                
                return Storage::disk('public')->url($this->image);
            },
        );
    }
}




