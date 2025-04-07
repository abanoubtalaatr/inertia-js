<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    use ApiResponse;

    /**
     * Get booking statistics for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingStatistics()
    {
        $userId = Auth::id();
        $userType = Auth::user()->type;

        $query = Booking::query();

        // Filter bookings based on user type
        if ($userType === 'client') {
            $query->where('client_id', $userId);
        } elseif ($userType === 'specialist') {
            $query->where('specialist_id', $userId);
        }

        $statistics = [
            'total_bookings' => $query->count(),
            'completed_bookings' => (clone $query)->where('status', 'completed')->count(),
            'cancelled_bookings' => (clone $query)->where('status', 'cancelled')->count(),
            'upcoming_bookings' => (clone $query)->where('status', 'pending')
                ->where('date', '>=', now()->toDateString())
                ->count(),
            'today_bookings' => (clone $query)->whereDate('date', now()->toDateString())->count(),
            'this_month_bookings' => (clone $query)->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        return $this->success($statistics);
    }
}
