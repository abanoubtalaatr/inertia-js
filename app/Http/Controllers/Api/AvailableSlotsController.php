<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailableSlotsController extends Controller
{
    public function index(User $user, Request $request)
    {
        // Validate the date input
        $request->validate([
            'date' => 'required|date_format:d-m-Y',
        ]);

        $date = Carbon::parse($request->input('date'));
        $slots = [];

        if ($user->opening_hours && $user->opening_hours->isOpenOn($date->format('l'))) {
            $ranges = $user->opening_hours->forDate($date);

            foreach ($ranges as $range) {
                // Ensure we're getting just the time string
                $startTime = is_array($range->start()) ? $range->start()[0] : $range->start();
                $endTime = is_array($range->end()) ? $range->end()[0] : $range->end();

                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);

                $slotStart = $start->copy();
                while ($slotStart->lt($end)) {
                    $slotEnd = $slotStart->copy()->addMinutes(30);
                    if ($slotEnd->lte($end)) {
                        $hasConflict = $user->appointments()
                            ->where('date', $date->format('Y-m-d'))
                            ->where(function ($query) use ($slotStart, $slotEnd) {
                                $query->where(function ($q) use ($slotStart) {
                                    $q->where('start_time', '<=', $slotStart->format('H:i'))
                                        ->where('end_time', '>', $slotStart->format('H:i'));
                                })->orWhere(function ($q) use ($slotStart, $slotEnd) {
                                    $q->where('start_time', '>=', $slotStart->format('H:i'))
                                        ->where('start_time', '<', $slotEnd->format('H:i'));
                                });
                            })
                            ->exists();

                        if (! $hasConflict) {
                            $slots[] = [
                                'start' => $slotStart->format('H:i'),
                                'end' => $slotEnd->format('H:i'),
                            ];
                        }
                    }
                    $slotStart->addMinutes(30);
                }
            }
        }

        return response()->json($slots);
    }
}
