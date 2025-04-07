<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentPlanResource extends JsonResource
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
            'client' => SimpleUserResource::make($this->client),
            'details' => $this->details,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'progress' => ProgressResource::collection($this->whenLoaded('progress')),
            'created_at' => $this->created_at,
        ];
    }
}
