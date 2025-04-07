<?php

namespace App\Http\Controllers\Api\Specialist;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProgressRequest;
use App\Http\Resources\Api\ProgressResource;
use App\Models\TreatmentPlan;

class ProgressController extends Controller
{
    use ApiResponse;

    public function store(ProgressRequest $request, TreatmentPlan $treatmentPlan)
    {
        $progress = $treatmentPlan->progress()->create([
            'notes' => $request->notes,
            'recorded_at' => now(),
        ]);

        return $this->success(ProgressResource::make($progress));
    }
}
