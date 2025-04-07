<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialist_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'feedback' => $this->faker->optional()->paragraph(),
        ];
    }

    // State for a specific specialist
    public function forSpecialist($specialistId)
    {
        return $this->state(function (array $attributes) use ($specialistId) {
            // Create a completed booking for this specialist
            $booking = Booking::factory()
                ->completed()
                ->forSpecialist($specialistId)
                ->create();

            return [
                'specialist_id' => $specialistId,
                'booking_id' => $booking->id,
                'user_id' => $booking->client_id, // Use the client from the booking
            ];
        });
    }

    // State for a specific rating value
    public function withRating($rating)
    {
        return $this->state(fn () => [
            'rating' => min(max($rating, 1), 5), // Ensure rating is between 1 and 5
        ]);
    }

    // State for ratings with feedback
    public function withFeedback()
    {
        return $this->state(fn () => [
            'feedback' => $this->faker->paragraph(),
        ]);
    }

    // State for ratings without feedback
    public function withoutFeedback()
    {
        return $this->state(fn () => [
            'feedback' => null,
        ]);
    }
}
