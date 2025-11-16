<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'arquivos' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'nomePaciente' => ['nullable', 'string', 'max:255'],
            'nomeMedico' => ['nullable', 'string', 'max:255'],
            'tipoDocumento' => ['nullable', 'string', 'max:255'],
            'dataDocumento' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
