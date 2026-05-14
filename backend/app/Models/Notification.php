<?php

namespace App\Models;

use App\Mail\NotificationMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function broadcast(int $userId, array $notification): void
    {
        $socketUrl = env('SOCKET_URL', 'http://signaling:3000');
        $ch = curl_init($socketUrl . '/notify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['userId' => $userId, 'notification' => $notification]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);
        curl_exec($ch);
        curl_close($ch);

        static::sendMail($userId, $notification);
    }

    public static function sendMail(int $userId, array $notification): void
    {
        try {
            $user = User::find($userId);
            if (!$user || !$user->email) {
                return;
            }
            Mail::to($user->email)->send(new NotificationMail($user, $notification));
        } catch (\Throwable $e) {
            \Log::warning('Failed to send notification email: ' . $e->getMessage());
        }
    }
}
