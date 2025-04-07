<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $type = request()->header('User-Type');
        if (! $type || ! in_array($type, ['provider', 'hotel'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'User-Type' => [__('auth.invalid_user_type')],
            ]);
        }

        return [
            'name' => 'required|string|min:6|max:255',
            'email' => 'required|string|email|max:255|unique:accounts',
            'phone' => ['nullable', 'string', 'regex:/^\+9665\d{8}$/', 'unique:accounts'], // رقم الهاتف
            'mobile' => ['required', 'string', 'regex:/^\+9665\d{8}$/', 'unique:accounts'], // رقم الجوال

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/[A-Z]/', // must contain at least one uppercase letter
                'regex:/[@$!%*?&#]/', // must contain at least one special character
            ],

            'commercial_register' => 'required|string|max:255|unique:accounts',

            'job_role_id' => isset($type) && $type === 'hotel' ? 'required|exists:job_roles,id' : '',

            // 'type' => 'required|in:provider,hotel',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'phone' => __('validation.attributes.phone'),
            'mobile' => __('validation.attributes.mobile'),
            'password' => __('validation.attributes.password'),
            'password_confirmation' => __('validation.attributes.password_confirmation'),
            'commercial_register' => __('validation.attributes.commercial_register'),
            'job_role_id' => __('validation.attributes.job_role_id'),
            // 'type' => __('Type'),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('validation.regex.phone'),
            'mobile.regex' => __('validation.regex.mobile'),
            'password.regex' => __('validation.regex.password'),
        ];
    }
}
