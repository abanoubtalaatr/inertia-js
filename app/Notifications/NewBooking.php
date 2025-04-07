<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBooking extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Booking Request')
            ->line('You have a new booking request.')
            ->line('Booking Details:')
            ->line('Date: '.$this->booking->date)
            ->line('Time: '.$this->booking->start_time.' - '.$this->booking->end_time)
            ->line('Client: '.$this->booking->client->name)
            ->action('View Booking', route('bookings.show', $this->booking->id))
            ->line('The meeting link will be available once confirmed.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_booking',
            'booking_id' => $this->booking->id,
            'message' => 'You have a new booking request from '.$this->booking->client->name,
        ];
    }
}
