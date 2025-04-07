<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20|unique:users',
            'address' => 'nullable|string',
            'preferred_language' => 'nullable|string|in:ar,en',
            'role' => 'required|string|in:user,specialist,company',
            'company_id' => 'required_if:role,user|exists:users,id',
            // For specialists
            'specialization' => 'required_if:role,specialist|string',
            'qualification' => 'required_if:role,specialist|string',
            'experience_years' => 'required_if:role,specialist|integer',
            'bio' => 'nullable|string',
            'company_id' => 'nullable|exists:users,id',
            'verification_documents.*' => 'required_if:role,specialist|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'available_days' => 'nullable|array',
            'available_hours' => 'nullable|array',
            // For companies
            'commercial_register' => 'required_if:role,company|string',
            'tax_number' => 'required_if:role,company|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
