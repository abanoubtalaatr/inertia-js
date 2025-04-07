<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
    public function rules()
    {

        $accountId = $this->account ? $this->account->id : null;
        $rules = [
            'name' => ['required', 'string', 'min:6', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => 'required|string|email|max:255|unique:accounts,email,'.$accountId,
            'phone' => ['nullable', 'string', 'regex:/^5\d{8}$/', 'unique:accounts,phone,'.$accountId], // رقم الهاتف عشان منساش
            'mobile' => ['required', 'string', 'regex:/^5\d{8}$/', 'unique:accounts,mobile,'.$accountId],
            'commercial_register' => 'required|string|max:255|unique:accounts,commercial_register,'.$accountId,
            'job_role_id' => 'required_if:type,hotel|exists:job_roles,id',
        ];

        switch ($this->method()) {
            case 'POST':
                $rules['password'] = [
                    $accountId ? 'nullable' : 'required',
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[@$!%*?&#]/', // must contain at least one special character
                ];
                break;
            case 'PUT':
            case 'PATCH':
                $rules['password'] = [
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[@$!%*?&#]/', // must contain at least one special character
                ];
                break;
        }

        return $rules;
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
