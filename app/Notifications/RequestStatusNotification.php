<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $type,
        protected string $status,
        protected ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = match ($this->status) {
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => $this->status,
        };

        $message = "O seu pedido de {$this->type} foi {$statusLabel}.";

        if ($this->status === 'rejected' && $this->reason) {
            $message .= " Motivo: {$this->reason}";
        }

        return [
            'title' => "Actualização de Pedido: {$this->type}",
            'message' => $message,
            'status' => $this->status,
            'type' => $this->type,
        ];
    }
}
