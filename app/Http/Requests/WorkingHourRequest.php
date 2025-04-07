<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust this if you need specific authorization
    }

    public function rules(): array
    {
        return [
            'days' => ['required', 'array'],
            'days.*.day_of_week' => ['required', 'string', 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'days.*.start_time' => ['nullable', 'date_format:H:i', 'required_unless:days.*.is_off,true'],
            'days.*.end_time' => ['nullable', 'date_format:H:i', 'after:days.*.start_time', 'required_unless:days.*.is_off,true'],
            'days.*.is_off' => ['boolean'], // True if the day is off
            'breaks' => ['nullable', 'array'],
            'breaks.*.start' => ['required', 'date_format:H:i', 'after:days.*.start_time', 'before:days.*.end_time'],
            'breaks.*.end' => ['required', 'date_format:H:i', 'after:breaks.*.start', 'before:days.*.end_time'],
            'holidays' => ['nullable', 'array'],
            'holidays.*.date' => ['required', 'date_format:Y-m-d'], // e.g., 2026-05-01
            'holidays.*.description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'days.required' => 'At least one day must be specified.',
            'days.*.day_of_week.required' => 'The day of the week is required.',
            'days.*.day_of_week.in' => 'The day must be a valid day (e.g., monday, tuesday, etc.).',
            'days.*.start_time.required_unless' => 'Start time is required unless the day is off.',
            'days.*.start_time.date_format' => 'Start time must be in HH:MM format (e.g., 08:00).',
            'days.*.end_time.required_unless' => 'End time is required unless the day is off.',
            'days.*.end_time.date_format' => 'End time must be in HH:MM format (e.g., 18:00).',
            'days.*.end_time.after' => 'End time must be after start time.',
            'days.*.is_off.boolean' => 'The "is off" field must be true or false.',
            'breaks.*.start.required' => 'Break start time is required.',
            'breaks.*.start.date_format' => 'Break start time must be in HH:MM format (e.g., 14:00).',
            'breaks.*.start.after' => 'Break start time must be after the day\'s start time.',
            'breaks.*.start.before' => 'Break start time must be before the day\'s end time.',
            'breaks.*.end.required' => 'Break end time is required.',
            'breaks.*.end.date_format' => 'Break end time must be in HH:MM format (e.g., 14:30).',
            'breaks.*.end.after' => 'Break end time must be after break start time.',
            'breaks.*.end.before' => 'Break end time must be before the day\'s end time.',
            'holidays.*.date.required' => 'Holiday date is required.',
            'holidays.*.date.date_format' => 'Holiday date must be in Y-m-d format (e.g., 2026-05-01).',
        ];
    }
}
