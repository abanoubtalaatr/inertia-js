<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name ?? '',
            'email' => $this->email ?? '',
            'phone' => $this->phone ?? '',
            'mobile' => $this->mobile ?? '',
            'is_profile_complete' => $this->type == 'provider' ? $this->isProviderProfileComplete() : $this->isHotelProfileComplete(),

            'comerical_register' => $this->commercial_register ?? '',

            'type' => $this->type,

        ];

        if ($this->type === 'hotel') {
            $data['job_role'] = $this->job_role_id ?? '';

        }

        return $data;
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
