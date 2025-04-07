<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treatment_plan_id' => TreatmentPlanResource::make($this->treatmentPlan),
            'notes' => $this->notes,
            'recorded_at' => Carbon::parse($this->recorded_at)->format('Y-m-d'),
        ];
    }
}
