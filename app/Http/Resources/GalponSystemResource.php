<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalponSystemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'galpon_id' => $this->galpon_id,
            'system_id' => $this->system_id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'technical_attributes_json' => $this->technical_attributes_json,
            'system' => $this->whenLoaded('system'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}