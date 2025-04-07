<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialist_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialist_id.required' => 'The specialist ID is required.',
            'specialist_id.exists' => 'The specified client does not exist.',
            'date.required' => 'The booking date is required.',
            'date.date_format' => 'The booking date must be in Y-m-d format (e.g., 2025-03-25).',
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in HH:MM format (e.g., 09:00).',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in HH:MM format (e.g., 09:30).',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
