<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceRequest extends FormRequest
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
            'notification_enabled' => 'sometimes|boolean',
            'aqi_threshold' => 'sometimes|integer|min:0|max:500',
            'preferred_language' => 'sometimes|string|max:10',
            'temperature_unit' => 'sometimes|in:celsius,fahrenheit',
        ];
    }
}
