<?php

namespace App\Http\Controllers\Api\Specialist;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TreatmentPlanRequest;
use App\Http\Resources\Api\TreatmentPlanResource;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $treatmentPlans = TreatmentPlan::query()->where('specialist_id', Auth::id());

        if ($request->filled('client_id')) {
            $treatmentPlans->where('client_id', $request->client_id);
        }

        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $data = TreatmentPlanResource::collection($treatmentPlans->paginate());

            return $this->success($data->response()->getData(true));
        }

        return $this->success(TreatmentPlanResource::collection($treatmentPlans->get()));
    }

    public function store(TreatmentPlanRequest $request)
    {
        $treatmentPlan = TreatmentPlan::create([
            'specialist_id' => Auth::id(),
            'client_id' => $request->client_id,
            'details' => $request->details,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $this->success(TreatmentPlanResource::make($treatmentPlan));
    }

    public function update(TreatmentPlanRequest $request, TreatmentPlan $treatmentPlan)
    {
        $treatmentPlan->update($request->only('details', 'start_date', 'end_date'));

        return $this->success(TreatmentPlanResource::make($treatmentPlan));
    }
}
