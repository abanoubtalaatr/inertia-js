<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpSent extends Notification implements ShouldQueue
{
    use Queueable;

    protected $otp;

    /**
     * Create a new notification instance.
     *
     * @param  string  $otp
     * @return void
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
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
        return (new MailMessage)
            ->subject('رمز التحقق الخاص بك')
            ->line('مرحباً بك في نظامنا.')
            ->line('رمز التحقق الخاص بك هو: '.$this->otp)
            ->line('هذا الرمز صالح لمدة 15 دقيقة فقط.')
            ->line('إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.');
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
            'title' => 'رمز التحقق الخاص بك',
            'body' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
        ];
    }
}
