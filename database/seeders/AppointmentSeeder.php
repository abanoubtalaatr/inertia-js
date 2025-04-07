<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments =
            [
                'user_id' => User::first()->id,
                'specialist_id' => User::find(27)->id,
                'date' => '18-03-2025',
                'start_time' => '09:00',
                'end_time' => '10:00',
            ];

        Appointment::created($appointments);
    }
}
