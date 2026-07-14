<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Visit $visit */
        $visit = $this->route('visit');

        $clientId = $this->input('client_id', $visit->client_id);
        $farmId = $this->input('farm_id', $visit->farm_id);

        return [
            'type' => ['sometimes', Rule::in(['diagnostico_tecnico'])],
            'status' => ['sometimes', Rule::in(['draft', 'completed'])],
            'client_id' => ['sometimes', 'integer', 'exists:clients,id'],
            'farm_id' => [
                'sometimes', 'integer',
                Rule::exists('farms', 'id')->where('client_id', $clientId),
            ],
            'galpon_id' => [
                'sometimes', 'integer',
                Rule::exists('galpones', 'id')->where('farm_id', $farmId),
            ],
            'fecha' => ['sometimes', 'date'],
            'num_aves' => ['nullable', 'integer', 'min:0'],
            'dia_lote' => ['nullable', 'integer', 'min:0'],
            'total_galpones' => ['nullable', 'integer', 'min:0'],
            'cliente_nombre' => ['nullable', 'string', 'max:255'],
            'granja_nombre' => ['nullable', 'string', 'max:255'],
            'galpon_numero' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'contacto' => ['sometimes', 'nullable', 'array'],
            'control' => ['sometimes', 'nullable', 'array'],
            'tablero' => ['sometimes', 'nullable', 'array'],
            'variables' => ['sometimes', 'nullable', 'array'],
            'ventilacion' => ['sometimes', 'nullable', 'array'],
            'mecanicos' => ['sometimes', 'nullable', 'array'],
            'evidencia' => ['sometimes', 'nullable', 'array'],
            'informe' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('control.sensores', []) as $key => $sensor) {
                if (! is_array($sensor)) {
                    continue;
                }

                $instalados = $sensor['instalados'] ?? null;
                $detectados = $sensor['detectados'] ?? null;

                if (is_numeric($instalados) && is_numeric($detectados) && $detectados > $instalados) {
                    $validator->errors()->add(
                        "control.sensores.{$key}.detectados",
                        'Los sensores detectados no pueden superar los instalados.'
                    );
                }
            }
        });
    }
}
