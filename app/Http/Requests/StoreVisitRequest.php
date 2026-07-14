<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['diagnostico_tecnico'])],
            'status' => ['nullable', Rule::in(['draft', 'completed'])],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'farm_id' => [
                'required', 'integer',
                Rule::exists('farms', 'id')->where('client_id', $this->input('client_id')),
            ],
            'galpon_id' => [
                'required', 'integer',
                Rule::exists('galpones', 'id')->where('farm_id', $this->input('farm_id')),
            ],
            'fecha' => ['required', 'date'],
            'num_aves' => ['nullable', 'integer', 'min:0'],
            'dia_lote' => ['nullable', 'integer', 'min:0'],
            'total_galpones' => ['nullable', 'integer', 'min:0'],
            'cliente_nombre' => ['nullable', 'string', 'max:255'],
            'granja_nombre' => ['nullable', 'string', 'max:255'],
            'galpon_numero' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'contacto' => ['nullable', 'array'],
            'control' => ['nullable', 'array'],
            'tablero' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
            'ventilacion' => ['nullable', 'array'],
            'mecanicos' => ['nullable', 'array'],
            'evidencia' => ['nullable', 'array'],
            'informe' => ['nullable', 'array'],
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
