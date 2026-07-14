<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'farm_id' => $this->farm_id,
            'galpon_id' => $this->galpon_id,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'num_aves' => $this->num_aves,
            'dia_lote' => $this->dia_lote,
            'cliente_nombre' => $this->cliente_nombre,
            'granja_nombre' => $this->granja_nombre,
            'galpon_numero' => $this->galpon_numero,
            'ubicacion' => $this->ubicacion,
            'total_galpones' => $this->total_galpones,
            'contacto' => $this->contacto_json,
            'control' => $this->control_json,
            'tablero' => $this->tablero_json,
            'variables' => $this->variables_json,
            'ventilacion' => $this->ventilacion_json,
            'mecanicos' => $this->mecanicos_json,
            'evidencia' => $this->evidencia_json,
            'informe' => $this->informe_json,
            'client' => new ClientResource($this->whenLoaded('client')),
            'farm' => new FarmResource($this->whenLoaded('farm')),
            'galpon' => new GalponResource($this->whenLoaded('galpon')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
