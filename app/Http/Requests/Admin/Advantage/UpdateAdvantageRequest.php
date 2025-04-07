<?php

namespace App\Http\Requests\Admin\Advantage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvantageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'translations' => 'nullable|array', // Changed to nullable
            'translations.*.title' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'translations.*.title.required' => 'The title for :key is required.',
        ];
    }
}
