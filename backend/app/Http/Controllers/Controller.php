<?php

namespace App\Http\Controllers;

use App\Models\Notification;

abstract class Controller
{
    protected function broadcastNotification(int $userId, array $notification): void
    {
        Notification::broadcast($userId, $notification);
    }
}


