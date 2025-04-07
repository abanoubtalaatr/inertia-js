<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TreatmentPlanResource;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $treatmentPlans = TreatmentPlan::where('client_id', Auth::id())->with('progress');

        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $data = TreatmentPlanResource::collection($treatmentPlans->paginate());

            return $this->success($data->response()->getData(true));
        }

        return $this->success(TreatmentPlanResource::collection($treatmentPlans->get()));
    }

    public function show(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->client_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return new TreatmentPlanResource($treatmentPlan->load('progress'));
    }
}
