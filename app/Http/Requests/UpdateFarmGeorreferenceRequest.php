<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmGeorreferenceRequest extends FormRequest
{
    use UppercasesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function uppercaseFields(): array
    {
        return ['address', 'town', 'department'];
    }

    public function rules(): array
    {
        return [
            'address' => ['sometimes', 'string', 'max:500'],
            'town' => ['sometimes', 'string', 'max:255'],
            'department' => ['sometimes', 'string', 'max:255'],
            'map_url_reference' => ['sometimes', 'string', 'max:1000'],
        ];
    }
}
