<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequiresApprovalNotification extends Notification
{
    use Queueable;

    public $typeItem;
    public $titleItem;
    public $messageItem;

    /**
     * Create a new notification instance.
     */
    public function __construct($typeItem, $titleItem, $messageItem)
    {
        $this->typeItem = $typeItem;
        $this->titleItem = $titleItem;
        $this->messageItem = $messageItem;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type_item' => $this->typeItem,
            'title' => $this->titleItem,
            'message' => $this->messageItem,
        ];
    }
}
