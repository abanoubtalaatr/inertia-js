<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $subject,
        public string $message
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->line($this->message)
            ->line('Booking Details:')
            ->line('Date: '.$this->booking->date)
            ->line('Time: '.$this->booking->start_time.' - '.$this->booking->end_time);

        if ($this->booking->meet_link) {
            $mail->action('Join Meeting', $this->booking->meet_link);
        }

        return $mail->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'booking_status_updated',
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'meet_link' => $this->booking->meet_link,
            'message' => $this->message,
        ];
    }
}
