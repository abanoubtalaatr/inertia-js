<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Booking Has Been Confirmed')
            ->line('Your booking has been successfully confirmed.')
            ->line('Meeting Details:')
            ->line('Date: '.$this->booking->date)
            ->line('Time: '.$this->booking->start_time.' - '.$this->booking->end_time)
            ->action('Join Meeting', $this->booking->meet_link)
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'meet_link' => $this->booking->meet_link,
            'message' => 'Your booking has been confirmed.',
        ];
    }
}
