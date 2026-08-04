<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => $this->data['type'] ?? 'system',
            'title'         => $this->data['title'] ?? 'Notifikasi Sistem',
            'description'   => $this->data['description'] ?? '',
            'changed_by'    => $this->data['changed_by'] ?? null,
            'created_at'    => now()->format('d/m/Y H:i'),
        ];
    }
}
