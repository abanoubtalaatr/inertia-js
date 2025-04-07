<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_profile_complete' => $this->type == 'provider' ? $this->isProviderProfileComplete() : $this->isHotelProfileComplete(),

            'type' => $this->type,
            'profile' => $this->type == 'provider' ? new ProviderProfileResource($this->provider) : new HotelProfileResource($this->hotel),
            // 'created_at' => $this->created_at->toDateTimeString(),
            // 'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    public function isHotelProfileComplete()
    {
        return $this->hotel && $this->hotel->hotel_name && $this->hotel->registration_number && $this->hotel->tax_number && $this->hotel->star_classification_certificate && $this->hotel->compliance_proof;
    }

    public function isProviderProfileComplete()
    {
        return $this->provider && $this->provider->company_name && $this->provider->registration_number && $this->provider->registration_country;

    }
}
