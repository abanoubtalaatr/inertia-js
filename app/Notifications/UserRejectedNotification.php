<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRejectedNotification extends Notification
{
    use Queueable;

    private $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail']; // You can add 'database' if you want to store notifications
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Rejected')
            ->line('We regret to inform you that your account has been rejected.')
            ->line('Reason: '.$this->reason)
            ->line('If you have any questions, please contact our support team.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your understanding.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your account has been rejected.',
            'reason' => $this->reason,
        ];
    }
}
