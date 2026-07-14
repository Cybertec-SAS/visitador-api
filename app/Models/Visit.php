<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'client_id',
        'farm_id',
        'galpon_id',
        'fecha',
        'num_aves',
        'dia_lote',
        'cliente_nombre',
        'granja_nombre',
        'galpon_numero',
        'ubicacion',
        'total_galpones',
        'contacto_json',
        'control_json',
        'tablero_json',
        'variables_json',
        'ventilacion_json',
        'mecanicos_json',
        'evidencia_json',
        'informe_json',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'contacto_json' => 'array',
            'control_json' => 'array',
            'tablero_json' => 'array',
            'variables_json' => 'array',
            'ventilacion_json' => 'array',
            'mecanicos_json' => 'array',
            'evidencia_json' => 'array',
            'informe_json' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function galpon(): BelongsTo
    {
        return $this->belongsTo(Galpon::class);
    }
}
