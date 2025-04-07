<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reason;

    /**
     * Create a new notification instance.
     *
     * @param  string|null  $reason
     * @return void
     */
    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('تم رفض طلب التسجيل')
            ->line('نأسف لإبلاغك بأنه تم رفض طلب التسجيل الخاص بك.');

        if ($this->reason) {
            $mail->line('السبب: '.$this->reason);
        }

        $mail->line('يمكنك التواصل مع فريق الدعم للحصول على مزيد من المعلومات.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'تم رفض طلب التسجيل',
            'body' => 'تم رفض طلب التسجيل الخاص بك'.($this->reason ? '. السبب: '.$this->reason : ''),
        ];
    }
}
