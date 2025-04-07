<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpeningHoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'opening_hours_data' => 'required|array',
            'opening_hours_data.saturday' => 'array',
            'opening_hours_data.sunday' => 'array',
            'opening_hours_data.monday' => 'array',
            'opening_hours_data.tuesday' => 'array',
            'opening_hours_data.wednesday' => 'array',
            'opening_hours_data.thursday' => 'array',
            'opening_hours_data.friday' => 'array',
            'opening_hours_data.exceptions' => 'array',
        ];
    }
}
