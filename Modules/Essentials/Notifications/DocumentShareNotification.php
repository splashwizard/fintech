<?php

namespace Modules\Essentials\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentShareNotification extends Notification
{
    use Queueable;

    protected $document;
    protected $shared_by;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($document, $shared_by)
    {
        $this->document = $document;
        $this->shared_by = $shared_by;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'document_name' => $this->document->name,
            'shared_by_name' => $this->shared_by->user_full_name,
            'shared_by_id' => $this->shared_by->id,
            'document_type' => $this->document->type
        ];
    }
}
