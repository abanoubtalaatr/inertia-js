<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
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
            'client_id' => SimpleUserResource::make($this->client),
            'medication' => $this->medication,
            'dosage' => $this->dosage,
            'instructions' => $this->instructions,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}
