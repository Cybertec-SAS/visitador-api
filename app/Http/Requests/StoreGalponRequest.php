<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalponRequest extends FormRequest
{
    use UppercasesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function uppercaseFields(): array
    {
        return [
            'name',
            'code',
            'description',
            'technical_attributes_json',
            'observations',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'under_construction', 'retired'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'dimensions_json' => ['nullable', 'array'],
            'dimensions_json.largo_m' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.ancho_m' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.altura_canal_m' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.altura_cumbrera_m' => ['nullable', 'numeric', 'min:0'],
            'technical_attributes_json' => ['nullable', 'array'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
