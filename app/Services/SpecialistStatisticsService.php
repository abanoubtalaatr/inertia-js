<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Rating;
use App\Models\User;

class SpecialistStatisticsService
{
    public function getStatistics($specialistId, $filters = [])
    {

        $specialist = User::with(['receivedRatings', 'specialistBookings.client', 'availabilities', 'holidays'])
            ->findOrFail($specialistId);

        // Apply filters
        $filteredData = $this->applyFilters($specialist, $filters);

        return [
            'total_bookings' => $filteredData['bookings']->count(),
            'completed_bookings' => $filteredData['bookings']->where('status', 'completed')->count(),
            'pending_bookings' => $filteredData['bookings']->where('status', 'pending')->count(),

            'cancelled_bookings' => $filteredData['bookings']->where('status', 'canceled')->count(),
            'not_show_bookings' => $filteredData['bookings']->where('status', 'now-show')->count(),

            'upcoming_bookings' => $filteredData['bookings']->where('status', 'confirmed')
                ->count(),
            'average_rating' => round($filteredData['ratings']->avg('rating'), 1),
            'total_clients' => $filteredData['bookings']->pluck('client_id')->unique()->count(),
            'ratings_distribution' => $this->getRatingsDistribution($filteredData['ratings']),
            // 'booking_status_distribution' => $this->getBookingStatusDistribution($filteredData['bookings']),
            'busiest_days' => $this->getBusiestDays($filteredData['bookings']),
            // 'availability_coverage' => $this->getAvailabilityCoverage($specialist),
        ];
    }

    private function applyFilters(User $specialist, array $filters)
    {
        $bookings = $specialist->specialistBookings()->with('client');
        $ratings = $specialist->receivedRatings();

        // Date range filter
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $bookings->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
            $ratings->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        } elseif (! empty($filters['time_range'])) {
            $dateRange = $this->getDateRangeFromFilter($filters['time_range']);
            $bookings->whereBetween('date', $dateRange);
            $ratings->whereBetween('created_at', $dateRange);
        }

        // Status filter
        if (! empty($filters['status'])) {
            $bookings->where('status', $filters['status']);
        }

        // Client search filter
        if (! empty($filters['client_search'])) {
            $bookings->whereHas('client', function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['client_search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['client_search'].'%');
            });

            $ratings->whereHas('user', function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['client_search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['client_search'].'%');
            });
        }

        // Rating filter
        if (! empty($filters['min_rating'])) {
            $ratings->where('rating', '>=', $filters['min_rating']);
        }

        return [
            'bookings' => $bookings->get(),
            'ratings' => $ratings->get(),
        ];
    }

    private function getDateRangeFromFilter($timeRange)
    {
        return match ($timeRange) {
            'today' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'week' => [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
            'month' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'year' => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default => [now()->subMonth()->format('Y-m-d'), now()->format('Y-m-d')],
        };
    }

    private function getRatingsDistribution($ratings)
    {
        return [
            '5_star' => $ratings->where('rating', 5)->count(),
            '4_star' => $ratings->where('rating', 4)->count(),
            '3_star' => $ratings->where('rating', 3)->count(),
            '2_star' => $ratings->where('rating', 2)->count(),
            '1_star' => $ratings->where('rating', 1)->count(),
            'total' => $ratings->count(),
        ];
    }

    private function getBookingStatusDistribution($bookings)
    {
        return [
            'completed' => $bookings->where('status', 'completed')->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'no_show' => $bookings->where('status', 'no_show')->count(),
        ];
    }

    private function getBusiestDays($bookings)
    {
        return $bookings->groupBy('date')
            ->map(function ($dayBookings) {
                return $dayBookings->count();
            })
            ->sortDesc()
            ->take(5)
            ->mapWithKeys(function ($count, $date) {
                return [\Carbon\Carbon::parse($date)->format('D, M j') => $count];
            });
    }

    private function getAvailabilityCoverage(User $specialist)
    {
        $workingDays = $specialist->availabilities->where('is_off', false)->count();
        $totalDays = 7; // Days in a week

        return [
            'working_days' => $workingDays,
            'off_days' => $totalDays - $workingDays,
            'coverage_percentage' => round(($workingDays / $totalDays) * 100),
        ];
    }
}
