<?php

namespace App\Http\Controllers\Api\Specialist;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\SpecialistStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialistStatisticsController extends Controller
{
    use ApiResponse;

    protected $statsService;

    public function __construct(SpecialistStatisticsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index(Request $request)
    {

        $filters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'client_search' => 'nullable|string',
            'min_rating' => 'nullable|integer|between:1,5',
            'time_range' => 'nullable|in:week,month,year',
        ]);

        $statistics = $this->statsService->getStatistics(Auth::id(), $filters);

        return $this->success($statistics);
    }
}
