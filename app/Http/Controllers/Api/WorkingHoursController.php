<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkingHourRequest;
use App\Services\WorkingHoursService;
use Illuminate\Http\Request;

class WorkingHoursController extends Controller
{
    protected $workingHoursService;

    public function __construct(WorkingHoursService $workingHoursService)
    {
        $this->workingHoursService = $workingHoursService;
    }

    public function store(WorkingHourRequest $request, $userId)
    {
        $this->workingHoursService->workingHours($request, $userId);

        return response()->json(['message' => 'تم تحديث ساعات العمل بنجاح'], 200);
    }

    public function index($userId)
    {
        $days = $this->workingHoursService->days($userId);

        return response()->json($days);
    }

    public function availableTimes(Request $request, $userId)
    {
        $date = $request->query('date');

        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'تاريخ غير صحيح. يرجى استخدام الصيغة Y-m-d'], 400);
        }

        $availableTimes = $this->workingHoursService->getAvailableTimes($userId, $date);

        return response()->json(['date' => $date, 'available_times' => $availableTimes]);
    }
}
