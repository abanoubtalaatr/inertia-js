<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startTime = $this->faker->time('H:i:s');
        $endTime = Carbon::parse($startTime)->addHour()->format('H:i:s');

        return [
            'specialist_id' => User::factory(),
            'client_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'rescheduled', 'no-show']),
        ];
    }

    // State for confirmed bookings
    public function confirmed()
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function completed()
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    // State for upcoming bookings
    public function upcoming()
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }

    // State for upcoming bookings
    public function canceled()
    {
        return $this->state(fn () => [
            'status' => 'canceled',
            'date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }

    // Bookings with a specific specialist
    public function forSpecialist($specialistId)
    {
        return $this->state(fn () => [
            'specialist_id' => $specialistId,
        ]);
    }

    // Bookings with a specific client
    public function forClient($clientId)
    {
        return $this->state(fn () => [
            'client_id' => $clientId,
        ]);
    }
}
