<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            'user' => SimpleUserResource::make($this->user),
            'specialist' => SimpleUserResource::make($this->specialist),
            // 'booking' => BookingResource::make($this->booking),
            'rating' => $this->rating,
            'feedback' => $this->feedback,
        ];
    }
}
