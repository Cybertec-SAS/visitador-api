<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalponSystemRequest extends FormRequest
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
        $galpon = $this->route('galpon');

        return [
            'system_id' => [
                'required',
                'integer',
                'exists:systems_catalog,id',
                Rule::unique('galpon_systems', 'system_id')->where(fn ($query) => $query->where('galpon_id', $galpon->id)),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'technical_attributes_json' => ['nullable', 'array'],
        ];
    }
}