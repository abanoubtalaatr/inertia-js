<?php

namespace App\Http\Controllers\Reports;

use App\Exports\UserActivityExport;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contract;
use App\Models\MainServiceProvider;
use App\Models\Provider;
use App\Models\ProviderSubscription;
use App\Models\SubService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $dateRange = [
            'start' => $request->get('start_date', now()->subMonth()->format('Y-m-d')),
            'end' => $request->get('end_date', now()->format('Y-m-d')),
        ];

        $summaryData = [
            'totalUsers' => [
                'hotels' => Account::where('type', 'hotel')->count(),
                'providers' => Account::where('type', 'provider')->count(),
                'growth' => 0,
            ],
            'revenue' => [
                'total' => 0,
                'breakdown' => [
                    'subscriptions' => 0,
                    'contracts' => 0,
                ],
                'growth' => 0,
            ],
            'subscriptions' => [
                'active' => 0,
                'total' => 0,
                'growth' => 0,
            ],
        ];

        $revenueData = [
            'trends' => [
                'labels' => [],
                'values' => [],
            ],
        ];

        $userGrowthData = [
            'registration' => [
                'labels' => [],
                'hotels' => [],
                'providers' => [],
            ],
        ];

        $serviceAnalytics = [
            'popular' => [],
            'categories' => [],
        ];

        return Inertia::render('Admin/Reports/Index', [
            'summaryData' => $this->getSummaryData($dateRange),
            'revenueData' => [
                'trends' => $this->getRevenueTrends($dateRange),
            ],
            'userGrowthData' => [
                'registration' => $this->getUserGrowthData($dateRange),
            ],
            'serviceAnalytics' => [
                'popular' => $this->getPopularServices($dateRange),
                'categories' => $this->getServiceCategories($dateRange),
            ],
        ]);
    }

    private function getDateRange($period)
    {
        $end = now();
        $start = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->startOfMonth()
        };

        return ['start' => $start, 'end' => $end];
    }

    private function getSummaryData($dateRange)
    {
        // Get Users Count
        $hotels = Account::where('type', 'hotel')->count();
        $providers = Account::where('type', 'provider')->count();

        // Get Active Contracts & Subscriptions
        $activeContracts = Contract::where('status', 'active')->count();
        $totalContracts = Contract::count();

        $activeSubscriptions = ProviderSubscription::where('status', 'active')->count();
        $totalSubscriptions = ProviderSubscription::count();

        // Calculate Revenue
        $subscriptionRevenue = ProviderSubscription::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');

        $contractsQuery = Contract::whereIn('status', ['active', 'pending_payment', 'pending_approval'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        $contractsTotalAmount = $contractsQuery->sum('total_amount');

        // dd($contractsTotalAmount);
        $systemCommission = $contractsQuery->sum('commission');
        $taxAmount = $contractsQuery->sum('vat');

        // Calculate Growth
        $previousPeriodStart = Carbon::parse($dateRange['start'])->subDays(
            Carbon::parse($dateRange['start'])->diffInDays(Carbon::parse($dateRange['end']))
        );

        $previousRevenue = ProviderSubscription::where('created_at', '<', $dateRange['start'])->sum('amount') +
            Contract::where('status', 'active')
                ->where('created_at', '<', $dateRange['start'])
                ->sum('total_amount');

        $currentRevenue = $subscriptionRevenue + $contractsTotalAmount;

        $revenueGrowth = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

        \Log::info('Revenue Breakdown:', [
            'subscriptions' => $subscriptionRevenue,
            'contracts_total' => $contractsTotalAmount,
            'system_commission' => $systemCommission,
            'tax_amount' => $taxAmount,
        ]);

        return [
            'totalUsers' => [
                'hotels' => $hotels,
                'providers' => $providers,
                'growth' => $this->calculateGrowthPercentage('accounts', $dateRange),
            ],
            'revenue' => [
                'total' => $currentRevenue,
                'breakdown' => [
                    'subscriptions' => $subscriptionRevenue,
                    'contracts' => $contractsTotalAmount,
                    'commission' => $systemCommission,
                    'tax' => $taxAmount,
                ],
                'growth' => $revenueGrowth,
            ],
            'subscriptions' => [
                'active' => $activeSubscriptions,
                'total' => $totalSubscriptions,
                'growth' => $this->calculateGrowthPercentage('subscriptions', $dateRange),
            ],
            'contracts' => [
                'active' => $activeContracts,
                'total' => $totalContracts,
                'growth' => $this->calculateGrowthPercentage('contracts', $dateRange),
            ],
        ];
    }

    private function getRevenueData($dateRange)
    {
        return [
            'trends' => $this->getRevenueTrends($dateRange),
            'distribution' => [
                'subscriptions' => DB::table('provider_subscriptions')
                    ->join('subscriptions', 'provider_subscriptions.subscription_id', '=', 'subscriptions.id')
                    ->join('subscription_translations', function ($join) {
                        $join->on('subscriptions.id', '=', 'subscription_translations.subscription_id')
                            ->where('subscription_translations.locale', '=', app()->getLocale());
                    })
                    ->select(
                        'subscription_translations.name as plan_name',
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(provider_subscriptions.amount) as total')
                    )
                    ->whereBetween('provider_subscriptions.created_at', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('subscriptions.id', 'subscription_translations.name')
                    ->get(),
                'contracts' => DB::table('contracts')
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'active')
                    ->groupBy('date')
                    ->get(),
            ],
        ];
    }

    private function getUserGrowthData($dateRange)
    {
        // Get all dates in range
        $dates = [];
        $current = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);

        while ($current <= $end) {
            $currentDate = $current->format('Y-m-d');
            $dates[$currentDate] = [
                'hotels' => 0,
                'providers' => 0,
            ];
            $current->addDay();
        }

        // Get hotel registrations by day
        $hotels = Account::where('type', 'hotel')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // Get provider registrations by day
        $providers = Account::where('type', 'provider')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // Fill in actual counts
        foreach ($hotels as $item) {
            if (isset($dates[$item->date])) {
                $dates[$item->date]['hotels'] = (int) $item->count;
            }
        }

        foreach ($providers as $item) {
            if (isset($dates[$item->date])) {
                $dates[$item->date]['providers'] = (int) $item->count;
            }
        }

        // Sort by date
        ksort($dates);

        \Log::info('User Growth Data:', [
            'dates' => $dates,
            'date_range' => $dateRange,
        ]);

        return [
            'labels' => array_keys($dates),
            'hotels' => array_column($dates, 'hotels'),
            'providers' => array_column($dates, 'providers'),
        ];
    }

    private function getServiceAnalytics($dateRange)
    {
        return [
            'popular' => SubService::withCount(['contracts' => function ($query) use ($dateRange) {
                $query->whereHas('contract', function ($q) use ($dateRange) {
                    $q->where('status', 'active')
                        ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                });
            }])
                ->with(['mainService', 'translations'])
                ->take(5)
                ->get(),
            'categories' => DB::table('main_service_providers')
                ->join('sub_services', 'main_service_providers.id', '=', 'sub_services.main_service_id')
                ->join('contract_services', 'sub_services.id', '=', 'contract_services.sub_service_id')
                ->join('contracts', 'contract_services.contract_id', '=', 'contracts.id')
                ->where('contracts.status', 'active')
                ->whereBetween('contracts.created_at', [$dateRange['start'], $dateRange['end']])
                ->select('main_services.id', 'main_services.name', DB::raw('COUNT(*) as count'))
                ->groupBy('main_services.id', 'main_services.name')
                ->get(),
        ];
    }

    private function calculateGrowthPercentage($type, $dateRange)
    {
        \Log::info("Calculating growth for {$type}", $dateRange);

        switch ($type) {
            case 'accounts':
                $current = Account::where('created_at', '>=', $dateRange['start'])
                    ->where('created_at', '<', $dateRange['end'])
                    ->count();

                $previous = Account::where('created_at', '<', $dateRange['start'])->count();
                break;

            case 'revenue':
                $current = ProviderSubscription::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                    ->where('created_at', '>=', $dateRange['start'])
                    ->where('created_at', '<', $dateRange['end'])
                    ->sum(DB::raw('COALESCE(amount, 0)')) +
                    Contract::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                        ->where('created_at', '>=', $dateRange['start'])
                        ->where('created_at', '<', $dateRange['end'])
                        ->sum(DB::raw('COALESCE(total_amount, 0)'));

                $previous = ProviderSubscription::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                    ->where('created_at', '<', $dateRange['start'])
                    ->sum(DB::raw('COALESCE(amount, 0)')) +
                    Contract::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                        ->where('created_at', '<', $dateRange['start'])
                        ->sum(DB::raw('COALESCE(total_amount, 0)'));
                break;

            case 'subscriptions':
                $current = ProviderSubscription::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                    ->where('created_at', '>=', $dateRange['start'])
                    ->where('created_at', '<', $dateRange['end'])
                    ->count();

                $previous = ProviderSubscription::whereIn('status', ['active', 'ACTIVE', 1, '1'])
                    ->where('created_at', '<', $dateRange['start'])
                    ->count();
                break;

            default:
                return 0;
        }

        \Log::info('Growth calculation results:', [
            'type' => $type,
            'current' => $current,
            'previous' => $previous,
        ]);

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getTotalRevenue($dateRange)
    {
        $subscriptionRevenue = ProviderSubscription::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->sum('amount');
        $contractRevenue = Contract::where('status', 'active')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total_amount');

        return $subscriptionRevenue + $contractRevenue;
    }

    private function getRevenueTrends($dateRange)
    {
        // Get all dates in range
        $dates = [];
        $current = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);

        while ($current <= $end) {
            $currentDate = $current->format('Y-m-d');
            $dates[$currentDate] = 0;
            $current->addDay();
        }

        // Get daily revenue from contracts and subscriptions combined
        $revenues = Contract::whereIn('status', ['active', 'pending_payment', 'pending_approval'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // Fill in revenue data
        foreach ($revenues as $item) {
            if (isset($dates[$item->date])) {
                $dates[$item->date] = (float) $item->total;
            }
        }

        // Sort by date
        ksort($dates);

        \Log::info('Revenue Trends:', [
            'data' => $dates,
        ]);

        return [
            'labels' => array_keys($dates),
            'values' => array_values($dates),
        ];
    }

    public function userActivity(Request $request)
    {
        // Get base query
        $query = $this->getFilteredAccounts($request);

        // Get statistics
        $statistics = [
            'total_users' => Account::count(),
            'active_users' => Account::whereNotNull('last_login_at')
                ->where('last_login_at', '>', now()->subDays(30))
                ->count(),
            'online_users' => Account::whereNotNull('last_login_at')
                ->where('last_login_at', '>', now()->subMinutes(5))
                ->count(),
            'hotels' => Account::where('type', 'hotel')->count(),
            'providers' => Account::where('type', 'provider')->count(),
        ];

        // Get results
        if ($request->has('export')) {
            $users = $this->transformAccountsData($query->get());

            return $this->exportReport($users, $statistics, $request->export);

        }

        // Get paginated results for table view
        $accounts = $query->orderBy('created_at', 'desc')->paginate(10);
        // dd($accounts);
        $users = $this->transformAccountsData($accounts);

        // Return Inertia view
        return Inertia::render('Admin/Reports/UserActivity', [
            'users' => $users,
            'statistics' => $statistics,
            'filters' => $this->getFilters($request),
            'pagination' => $this->getPaginationData($accounts),
        ]);
    }

    private function getFilteredAccounts(Request $request)
    {
        $query = Account::query()
            ->select(['id', 'name', 'email', 'type', 'created_at', 'last_login_at', 'is_active', 'phone']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('activity')) {
            switch ($request->activity) {
                case 'online':
                    $query->whereNotNull('last_login_at')
                        ->where('last_login_at', '>', now()->subMinutes(5));
                    break;
                case 'active':
                    $query->where('is_active', 1);
                    break;
                case 'inactive':
                    $query->where('is_active', 0);
                    break;
            }
        }

        return $query;
    }

    private function transformAccountsData($accounts)
    {
        return $accounts->map(function ($account) {
            $lastLogin = $account->last_login_at ? Carbon::parse($account->last_login_at) : null;
            $isOnline = $account->isOnline();

            // dd($account);
            return [
                'user_id' => $account->id,
                'full_name' => $account->name,
                'email' => $account->email,
                'registration_date' => $account->created_at->format('Y-m-d'),
                'last_login' => $lastLogin ? $lastLogin->format('Y-m-d H:i:s') : '-',
                'is_online' => $isOnline,
                'activity_status' => $lastLogin ? $lastLogin->diffForHumans(['locale' => 'ar']) : '-',
                'user_type' => $account->type === 'hotel' ? 'فندق' : 'مزود خدمة',
                'status' => $account->is_active === 1 ? 'نشط' : 'غير نشط',
                'number_of_contracts' => $this->getContractsCount($account),
                'subscription_plan' => $this->getSubscriptionPlan($account),
            ];
        });
    }

    private function getContractsCount($account)
    {
        return $account->type === 'hotel' ?
            Contract::where('hotel_id', $account->id)->count() :
            '-';
    }

    private function getSubscriptionPlan($account)
    {
        if ($account->type !== 'provider') {
            return '-';
        }

        return ProviderSubscription::where('account_id', $account->id)
            ->latest()
            ->first()?->plan_name ?? 'Free';
    }

    private function exportToExcel($users)
    {
        return Excel::download(
            new UserActivityExport($users),
            'تقرير_نشاط_المستخدمين_'.now()->format('Y-m-d').'.xlsx'
        );
    }

    private function exportToPdf($users)
    {
        $pdf = PDF::loadView('exports.user-activity-pdf', [
            'users' => $users,
            'date' => now()->format('Y-m-d'),
        ]);

        return $pdf->download('تقرير_نشاط_المستخدمين_'.now()->format('Y-m-d').'.pdf');
    }

    private function exportReport($users, $statistics, $type)
    {
        if ($type === 'excel') {
            return Excel::download(
                new UserActivityExport($report),
                'user_activity_report_'.now()->format('Y-m-d').'.xlsx'
            );
        }

        $pdf = PDF::loadView('exports.user-activity-pdf', [
            'users' => $users,
            'statistics' => $statistics,
            'date' => now()->format('Y-m-d'),
        ]);

        return $pdf->download('user_activity_report_'.now()->format('Y-m-d').'.pdf');
    }

    private function getFilters(Request $request)
    {
        return [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
            'status' => $request->status,
        ];
    }

    private function getPaginationData($accounts)
    {
        return [
            'current_page' => $accounts->currentPage(),
            'per_page' => $accounts->perPage(),
            'total' => $accounts->total(),
            'last_page' => $accounts->lastPage(),
        ];
    }

    public function providerPerformance(Request $request)
    {
        $filters = $request->validate([
            'dateRange' => 'nullable|array',
            'mainService' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);

        $report = $this->reportService->getProviderPerformanceReport($filters);

        if ($request->has('export')) {
            return $this->reportService->exportReport('provider-performance', $report, $request->export);
        }

        return Inertia::render('Admin/Reports/ProviderPerformance', [
            'report' => $report,
        ]);
    }

    private function getPopularServices($dateRange)
    {
        return SubService::withCount(['contractServices' => function ($query) use ($dateRange) {
            $query->whereHas('contract', function ($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->whereIn('status', ['active', 'pending_payment', 'pending_approval']);
            });
        }])
            ->with(['mainService'])
            ->having('contract_services_count', '>', 0)
            ->orderByDesc('contract_services_count')
            ->limit(10)
            ->get()
            ->map(function ($service) {
                return [
                    'name' => $service->translation?->name ?? $service->name ?? 'غير معروف',
                    'category' => $service->mainService?->mainService?->name ?? 'غير معروف',
                    'count' => $service->contract_services_count,
                ];
            });
    }

    private function getServiceCategories($dateRange)
    {
        return MainServiceProvider::withCount(['subServices' => function ($query) use ($dateRange) {
            $query->whereHas('contractServices', function ($q) use ($dateRange) {
                $q->whereHas('contract', function ($q2) use ($dateRange) {
                    $q2->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                        ->whereIn('status', ['active', 'pending_payment', 'pending_approval']);
                });
            });
        }])
            ->having('sub_services_count', '>', 0)
            ->orderByDesc('sub_services_count')
            ->get()
            ->map(function ($service) {
                return [
                    'name' => $service->name ?? $service->service->name ?? 'غير معروف',
                    'count' => $service->sub_services_count,
                ];
            });
    }
}
