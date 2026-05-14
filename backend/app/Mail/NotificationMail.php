<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $body;
    public ?string $actionUrl;
    public ?string $actionText;

    public function __construct(
        public User $user,
        public array $notificationData,
    ) {
        $type = $notificationData['type'] ?? '';
        $data = $notificationData['data'] ?? [];

        $this->title = match ($type) {
            'answer' => 'Han respondido a tu pregunta',
            'answer_useful' => 'Tu respuesta fue marcada como útil',
            'meeting' => 'Nueva charla programada',
            'private_message' => 'Nuevo mensaje privado',
            'meeting_message' => 'Nuevo mensaje en charla',
            'group_message' => 'Nuevo mensaje en grupo',
            default => 'Notificación de TelamoNet',
        };

        $this->body = match ($type) {
            'answer' => ($data['user_name'] ?? 'Alguien') . ' ha respondido a tu pregunta "' . ($data['question_title'] ?? '') . '"',
            'answer_useful' => 'Tu respuesta en "' . ($data['question_title'] ?? '') . '" ha sido marcada como útil por ' . ($data['user_name'] ?? 'alguien'),
            'meeting' => 'Se ha programado la charla "' . ($data['meeting_name'] ?? '') . '" para el ' . ($data['schedule'] ?? ''),
            'private_message' => ($data['sender_name'] ?? 'Alguien') . ' te ha enviado: "' . ($data['snippet'] ?? '') . '"',
            'meeting_message' => ($data['sender_name'] ?? 'Alguien') . ' en "' . ($data['meeting_name'] ?? '') . '": "' . ($data['snippet'] ?? '') . '"',
            'group_message' => ($data['sender_name'] ?? 'Alguien') . ' en "' . ($data['group_name'] ?? '') . '": "' . ($data['snippet'] ?? '') . '"',
            default => 'Tienes una nueva notificación en TelamoNet',
        };

        $this->actionUrl = match ($type) {
            'private_message' => config('app.frontend_url') . '/private-chat?user=' . ($data['sender_id'] ?? ''),
            'meeting_message' => config('app.frontend_url') . '/meetingchat/' . ($data['meeting_id'] ?? ''),
            'group_message' => config('app.frontend_url') . '/group-chat/' . ($data['group_id'] ?? ''),
            'meeting' => config('app.frontend_url') . '/meeting',
            default => config('app.frontend_url') . '/notification',
        };

        $this->actionText = match ($type) {
            'private_message', 'meeting_message', 'group_message' => 'Ver mensaje',
            'meeting' => 'Ver charla',
            default => 'Ver notificaciones',
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->title . ' - TelamoNet');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notification');
    }

    public function attachments(): array
    {
        return [];
    }
}
