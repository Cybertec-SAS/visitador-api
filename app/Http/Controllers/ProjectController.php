<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    private const TIPOS = [
        'SOLUCION TOTAL',
        'AMBIENTE CONTROLADO',
        'AMBIENTE ABIERTO',
    ];

    private const LINEAS = [
        'AVICULTURA: LEVANTE Y PRODUCCION',
        'AVICULTURA: ENGORDE DE POLLO',
        'PORCICULTURA',
        'BOVINO',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['client', 'farm'])
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->farm_id, fn($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', mb_strtoupper((string) $request->tipo, 'UTF-8')))
            ->when($request->filled('linea'), fn($q) => $q->where('linea', mb_strtoupper((string) $request->linea, 'UTF-8')))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedPayload($request, false);

        return response()->json(Project::create($validated)->load(['client', 'farm']), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load(['client', 'farm', 'progressReports']));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $this->validatedPayload($request, true);

        $project->update($validated);

        return response()->json($project->load(['client', 'farm']));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }

    private function validatedPayload(Request $request, bool $isUpdate): array
    {
        $payload = $request->all();

        foreach (['name', 'code', 'tipo', 'linea', 'description'] as $field) {
            if (! array_key_exists($field, $payload) || ! is_string($payload[$field])) {
                continue;
            }

            $payload[$field] = mb_strtoupper($payload[$field], 'UTF-8');
        }

        return Validator::make($payload, $isUpdate ? [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'string', Rule::in(self::TIPOS)],
            'linea' => ['nullable', 'string', Rule::in(self::LINEAS)],
            'status' => ['nullable', Rule::in(['draft', 'active', 'paused', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ] : [
            'client_id' => ['required', 'exists:clients,id'],
            'farm_id' => ['required', 'exists:farms,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'string', Rule::in(self::TIPOS)],
            'linea' => ['nullable', 'string', Rule::in(self::LINEAS)],
            'status' => ['nullable', Rule::in(['draft', 'active', 'paused', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
        ])->validate();
    }
}
