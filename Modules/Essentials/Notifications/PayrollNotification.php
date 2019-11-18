<?php

namespace Modules\Essentials\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayrollNotification extends Notification
{
    use Queueable;

    protected $payroll;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($payroll)
    {
        $this->payroll = $payroll;
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
    public function toArray($notifiable)
    {
        $transaction_date = \Carbon::parse($this->payroll->transaction_date);
        return [
            "month" => $transaction_date->format('m'),
            "year" => $transaction_date->format('Y'),
            "ref_no" => $this->payroll->ref_no,
            "action" => $this->payroll->action,
            'created_by' => $this->payroll->created_by
        ];
    }
}
