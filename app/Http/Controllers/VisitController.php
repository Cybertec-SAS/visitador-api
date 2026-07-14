<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Section keys exposed by the API (sin sufijo) mapped to their `*_json` columns.
     */
    private const SECTIONS = [
        'contacto',
        'control',
        'tablero',
        'variables',
        'ventilacion',
        'mecanicos',
        'evidencia',
        'informe',
    ];

    public function index(Request $request): JsonResponse
    {
        $visits = Visit::query()
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->farm_id, fn($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest('fecha')
            ->latest('id')
            ->paginate($request->per_page ?? 15);

        return VisitResource::collection($visits)->response();
    }

    public function store(StoreVisitRequest $request): JsonResponse
    {
        $visit = Visit::create($this->persisted($request->validated()));

        return (new VisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Visit $visit): VisitResource
    {
        return new VisitResource($visit);
    }

    public function update(UpdateVisitRequest $request, Visit $visit): VisitResource
    {
        $visit->update($this->persisted($request->validated()));

        return new VisitResource($visit);
    }

    public function destroy(Visit $visit): JsonResponse
    {
        $visit->delete();

        return response()->json(null, 204);
    }

    /**
     * Rename the un-suffixed section keys to their `*_json` columns for persistence.
     */
    private function persisted(array $data): array
    {
        foreach (self::SECTIONS as $section) {
            if (array_key_exists($section, $data)) {
                $data[$section.'_json'] = $data[$section];
                unset($data[$section]);
            }
        }

        return $data;
    }
}
