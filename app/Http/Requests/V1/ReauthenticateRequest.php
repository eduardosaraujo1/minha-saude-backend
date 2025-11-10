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
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'authType' => 'required|in:google,email',
            'auth' => 'required|array',
            'auth.google' => 'nullable|array',
            'auth.google.oauthToken' => 'required_if:authType,google|string',
            'auth.email' => 'nullable|array',
            'auth.email.email' => 'required_if:authType,email|email',
            'auth.email.code' => 'required_if:authType,email|digits:6',
        ];
    }
}
