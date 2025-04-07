<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role; // Remove if no Contact model exists

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Basic counts
        $userCount = User::when(Auth::user()->role == 'company', function ($user) {
            $user->where('company_id', Auth::id());
        })->count();
        $bookingsCount = Booking::when(Auth::user()->role === 'company', function ($query) {
            $query->whereHas('client', function ($clientQuery) {
                $clientQuery->where('company_id', Auth::id());
            });
        })->count();
        $rolesCount = Role::where('is_active', 1)->count();

        // Data with date range filtering
        $summaryData = $this->getSummaryData($startDate, $endDate);
        $bookingsData = $this->getBookingsTrends($startDate, $endDate);
        $userGrowthData = $this->getUserGrowthData($startDate, $endDate);

        return Inertia::render('Dashboard', [
            'userCount' => $userCount,
            'bookingsCount' => $bookingsCount,
            'rolesCount' => $rolesCount,
            'summaryData' => $summaryData,
            'bookingsData' => $bookingsData,
            'userGrowthData' => $userGrowthData,
            'role' => Auth::user()->role,
        ]);
    }

    private function getSummaryData($startDate, $endDate)
    {
        $query = $startDate && $endDate
            ? fn ($q) => $q->whereBetween('created_at', [\Carbon\Carbon::parse($startDate)->startOfDay(), \Carbon\Carbon::parse($endDate)->endOfDay()])
            : fn ($q) => $q;

        // Users by role (using 'role' column directly)
        $specialists = User::where('role', 'specialist')->where('is_active', 1)->count();
        $companies = User::where('role', 'company')->where('is_active', 1)->count();
        $admins = User::where('role', 'admin')->where('is_active', 1)->count();
        $usersGrowth = $this->calculateGrowth(User::class, $startDate, $endDate);

        // Bookings
        $bookingsTotal = Booking::when(Auth::user()->role === 'company', function ($query) {
            $query->whereHas('client', function ($clientQuery) {
                $clientQuery->where('company_id', Auth::id());
            });
        })->when($query, $query)->count();
        $bookingsGrowth = $this->calculateGrowth(Booking::class, $startDate, $endDate);

        // Contacts (remove this block if no Contact model exists)
        $contactsTotal = Contact::when($query, $query)->count();
        $contactsGrowth = $this->calculateGrowth(Contact::class, $startDate, $endDate);

        return [
            'users' => [
                'specialists' => $specialists ?? 0,
                'companies' => $companies ?? 0,
                'admins' => $admins ?? 0,
                'growth' => $usersGrowth ?? 0,
            ],
            'bookings' => [
                'total' => $bookingsTotal ?? 0,
                'growth' => $bookingsGrowth ?? 0,
            ],
            'contacts' => [
                'total' => $contactsTotal ?? 0,
                'growth' => $contactsGrowth ?? 0,
            ],
        ];
    }

    private function getBookingsTrends($startDate, $endDate)
    {
        if (! $startDate || ! $endDate) {
            $startDate = now()->subMonth()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        $bookings = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return [
            'trends' => [
                'labels' => $bookings->pluck('date')->values()->all(),
                'values' => $bookings->pluck('total')->values()->all(),
            ],
        ];
    }

    private function getUserGrowthData($startDate, $endDate)
    {
        if (! $startDate || ! $endDate) {
            $startDate = now()->subMonth()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Fetch growth trends for specialists, companies, and admins
        $specialists = User::where('role', 'specialist')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $companies = User::where('role', 'company')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $admins = User::where('role', 'admin')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Merge all unique dates from specialists, companies, and admins
        $labels = $specialists->pluck('date')
            ->merge($companies->pluck('date'))
            ->merge($admins->pluck('date'))
            ->unique()
            ->sort()
            ->values();

        return [
            'registration' => [
                'labels' => $labels->all(),
                'specialists' => $labels->map(fn ($date) => $specialists->where('date', $date)->first()->total ?? 0)->all(),
                'companies' => $labels->map(fn ($date) => $companies->where('date', $date)->first()->total ?? 0)->all(),
                'admins' => $labels->map(fn ($date) => $admins->where('date', $date)->first()->total ?? 0)->all(),

            ],
        ];
    }

    private function calculateGrowth($model, $startDate, $endDate)
    {
        if (! $startDate || ! $endDate) {
            return 0;
        }

        $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

        $currentPeriod = $model::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousStart = \Carbon\Carbon::parse($startDate)->subDays(\Carbon\Carbon::parse($endDate)->diffInDays($startDate))->toDateString();
        $previousEnd = \Carbon\Carbon::parse($startDate)->subDay()->toDateString();
        $previousPeriod = $model::whereBetween('created_at', [$previousStart, $previousEnd])->count();

        if ($previousPeriod == 0) {
            return $currentPeriod > 0 ? 100 : 0;
        }

        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2);
    }
}
