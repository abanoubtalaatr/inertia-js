<?php

namespace App\Services;

use App\Http\Requests\WorkingHourRequest;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Holiday;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Log;

class WorkingHoursService
{
    protected $sessionDuration;

    protected $bufferTime;

    public function days($userId)
    {
        $availabilities = Availability::with('breakTimes')->where('user_id', $userId)->get();

        $holidays = Holiday::where('user_id', $userId)->get()->map(function ($holiday) {
            return [
                'date' => $holiday->holiday_date,
                'description' => $holiday->description,
            ];
        });

        $days = [];
        foreach ($availabilities as $day) {
            $dayData = [
                'day' => $day->day_of_week,
                'day_name' => __($day->day_of_week),
                'is_off' => $day->is_off,
                'start_time' => $day->start_time,
                'end_time' => $day->end_time,
                'breaks' => $day->breakTimes->map(function ($break) {
                    return [
                        'start' => $break->start_time,
                        'end' => $break->end_time,
                    ];
                })->toArray(),
            ];

            $days[] = $dayData;
        }

        return [
            'working_days' => $days,
            'holidays' => $holidays,
            'settings' => [
                'session_duration' => User::find($userId)->session_duration ?? 45,
                'buffer_time' => User::find($userId)->buffer_time ?? 15,
            ],
        ];
    }

    public function workingHours(WorkingHourRequest $request, $userId)
    {
        $user = User::findOrFail($userId);
        $this->sessionDuration = $user->session_duration;
        $this->bufferTime = $user->buffer_time;

        // Delete existing data
        Availability::where('user_id', $userId)->delete();

        foreach ($request->input('days') as $dayData) {
            $day = $dayData['day_of_week'];
            $isOff = $dayData['is_off'] ?? false;

            $availability = Availability::create([
                'user_id' => $userId,
                'day_of_week' => $day,
                'start_time' => $isOff ? null : $dayData['start_time'],
                'end_time' => $isOff ? null : $dayData['end_time'],
                'is_off' => $isOff,
            ]);

            // Add breaks only if day is not off and breaks are provided for this day
            if (! $isOff && isset($dayData['breaks'])) {
                foreach ($dayData['breaks'] as $break) {
                    $availability->breakTimes()->create([
                        'start_time' => $break['start'],
                        'end_time' => $break['end'],
                    ]);
                }
            }
        }

        // Handle holidays
        if ($request->has('holidays')) {
            Holiday::where('user_id', $userId)->delete();
            foreach ($request->input('holidays') as $holidayData) {
                Holiday::create([
                    'user_id' => $userId,
                    'holiday_date' => $holidayData['date'],
                    'description' => $holidayData['description'] ?? null,
                ]);
            }
        }
    }

    public function getAvailableTimes($userId, $date)
    {
        $dateObj = new DateTime($date);
        $dayOfWeek = strtolower($dateObj->format('l'));
        $user = User::find($userId);
        $this->sessionDuration = $user->session_duration;
        $this->bufferTime = $user->buffer_time;
        // Check if it's a holiday
        if (Holiday::where('user_id', $userId)->where('holiday_date', $date)->exists()) {
            return [];
        }

        // Get availability for this day
        $availability = Availability::with('breakTimes')
            ->where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $availability || $availability->is_off || ! $availability->start_time || ! $availability->end_time) {
            return [];
        }

        // Get confirmed bookings
        $bookings = Booking::where('specialist_id', $userId)
            ->where('date', $date)
            ->where('status', 'confirmed')
            ->get();

        // Generate all possible time slots
        $allSlots = $this->generateTimeSlotsWithBreaks(
            $availability->start_time,
            $availability->end_time,
            $this->getBreaksFromAvailability($availability)
        );

        // Filter out booked slots
        return $this->filterBookedSlots($allSlots, $bookings);
    }

    private function getBreaksFromAvailability($availability)
    {
        if (! $availability->relationLoaded('breakTimes')) {
            $availability->load('breakTimes');
        }

        return $availability->breakTimes->map(function ($break) {
            return [
                'start' => $break->start_time,
                'end' => $break->end_time,
            ];
        })->toArray();
    }

    private function generateTimeSlotsWithBreaks($startTime, $endTime, $breaks = [])
    {
        $slots = [];

        try {
            $start = new DateTime($startTime);
            $end = new DateTime($endTime);
        } catch (\Exception $e) {
            Log::error("Invalid time format: start=$startTime, end=$endTime");

            return [];
        }

        // Ensure session duration and buffer time are properly set
        $sessionDuration = $this->sessionDuration ?? 30; // default to 30 minutes if not set
        $bufferTime = $this->bufferTime ?? 0; // default to 0 if not set

        // Prepare break intervals with validation
        $breakIntervals = [];
        foreach ($breaks as $break) {
            try {
                $breakStart = new DateTime($break['start']);
                $breakEnd = new DateTime($break['end']);

                // Validate break period
                if ($breakEnd <= $breakStart) {
                    Log::warning("Invalid break period: {$break['start']} - {$break['end']}");

                    continue;
                }

                $breakIntervals[] = [
                    'start' => $breakStart,
                    'end' => $breakEnd,
                ];
            } catch (\Exception $e) {
                Log::warning('Invalid break time format: '.json_encode($break));

                continue;
            }
        }

        // Sort breaks by start time
        usort($breakIntervals, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        $currentBreakIndex = 0;
        $breakCount = count($breakIntervals);

        while ($start < $end) {
            // Check if we're currently within a break period
            if ($currentBreakIndex < $breakCount && $start >= $breakIntervals[$currentBreakIndex]['start']) {
                // Skip to end of this break
                $start = clone $breakIntervals[$currentBreakIndex]['end'];
                $currentBreakIndex++;

                continue;
            }

            // Calculate slot end time
            $slotEnd = clone $start;
            $slotEnd->modify("+{$sessionDuration} minutes");

            // If slot goes beyond working hours, stop
            if ($slotEnd > $end) {
                break;
            }

            // Check if this slot overlaps with any upcoming break
            $overlapsBreak = false;
            for ($i = $currentBreakIndex; $i < $breakCount; $i++) {
                if ($slotEnd > $breakIntervals[$i]['start']) {
                    $overlapsBreak = true;
                    // Move start to the break start time (we'll skip the break in next iteration)
                    $start = clone $breakIntervals[$i]['start'];
                    break;
                }
            }

            if (! $overlapsBreak) {
                // Add this valid slot
                $slots[] = [
                    'start' => $start->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'duration' => $sessionDuration,
                ];

                // Move to next potential slot start (including buffer time)
                $start = clone $slotEnd;
                if ($bufferTime > 0) {

                    $start->modify("+{$bufferTime} minutes");
                }
            }
        }

        return $slots;
    }

    private function isTimeOverlap($slotStart, $slotEnd, $breakStart, $breakEnd)
    {
        return $slotStart < $breakEnd && $slotEnd > $breakStart;
    }

    private function filterBookedSlots($slots, $bookings)
    {
        return array_values(array_filter($slots, function ($slot) use ($bookings) {
            foreach ($bookings as $booking) {
                $bookingStart = new DateTime($booking->start_time);
                $bookingEnd = new DateTime($booking->end_time);
                $slotStart = new DateTime($slot['start']);
                $slotEnd = new DateTime($slot['end']);

                if ($this->isTimeOverlap($slotStart, $slotEnd, $bookingStart, $bookingEnd)) {
                    return false;
                }
            }

            return true;
        }));
    }
}
