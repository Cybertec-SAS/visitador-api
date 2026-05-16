<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'description' => $this->description,
            'dimensions_json' => $this->dimensions_json,
            'technical_attributes_json' => $this->technical_attributes_json,
            'observations' => $this->observations,
            'sort_order' => $this->sort_order,
            'systems' => GalponSystemResource::collection($this->whenLoaded('systems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
