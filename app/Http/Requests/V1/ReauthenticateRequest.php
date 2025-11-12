<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReauthenticateRequest extends FormRequest
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
        $rules = [
            'auth' => 'required|array',
        ];

        // If auth.google is present, validate its structure
        if ($this->has('auth.google')) {
            $rules['auth.google'] = 'required|array';
            $rules['auth.google.oauthToken'] = 'required|string';
        }

        // If auth.email is present, validate its structure
        if ($this->has('auth.email')) {
            $rules['auth.email'] = 'required|array';
            $rules['auth.email.email'] = 'required|email';
            $rules['auth.email.code'] = 'required|string';
        }

        return $rules;
    }
}
