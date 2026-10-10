<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Kabar di lonceng dashboard bahwa ada pesan baru dari formulir kontak website. Di-queue supaya
 * pengunjung tidak menunggu (dan Reverb yang mati tidak menggagalkan pengiriman formulir).
 */
class ContactMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'inbox',
            'color' => 'amber',
            'message' => "Pesan baru dari {$this->contactMessage->name}: {$this->contactMessage->topicLabel()}.",
            'url' => route('content.messages', absolute: false),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
