<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGalponSystemRequest extends FormRequest
{
    use UppercasesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function uppercaseFields(): array
    {
        return [
            'notes',
            'technical_attributes_json',
        ];
    }

    public function rules(): array
    {
        $galponSystem = $this->route('galponSystem');

        return [
            'system_id' => [
                'sometimes',
                'integer',
                'exists:systems_catalog,id',
                Rule::unique('galpon_systems', 'system_id')
                    ->where(fn ($query) => $query->where('galpon_id', $galponSystem->galpon_id))
                    ->ignore($galponSystem->id),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'technical_attributes_json' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
