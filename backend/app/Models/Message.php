<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'meeting_id',
        'user_id',
        'group_id',
        'meeting_id',
        'content',
        'message_type',
        'file_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
