<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    use UppercasesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function uppercaseFields(): array
    {
        return ['razon_social', 'nit'];
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['sometimes', 'string', 'max:255'],
            'nit' => ['sometimes', 'string', 'max:50', 'unique:clients,nit,' . $this->client?->id],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
