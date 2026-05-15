<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStructureRequest;
use App\Http\Requests\UpdateStructureRequest;
use App\Http\Resources\StructureResource;
use App\Models\Project;
use App\Models\Structure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Structure::with(['parent', 'systems'])
            ->when($request->farm_id, fn($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->filled('structure_type'), fn($q) => $q->where('structure_type', mb_strtoupper((string) $request->structure_type, 'UTF-8')))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->boolean('parent_only'), fn($q) => $q->whereNull('parent_structure_id')->where('structure_type', Structure::TYPE_GALPON))
            ->orderBy('sort_order')
            ->orderBy('name');

        return response()->json(StructureResource::collection($query->get())->resolve());
    }

    public function indexByProject(Project $project): JsonResponse
    {
        $structures = $project->structures()->with(['parent', 'systems'])->orderBy('sort_order')->orderBy('name')->get();

        return response()->json(StructureResource::collection($structures)->resolve());
    }

    public function syncProjectStructures(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'structure_ids' => 'required|array',
            'structure_ids.*' => 'integer|exists:structures,id',
        ]);

        $project->structures()->sync($validated['structure_ids']);

        $structures = $project->structures()->with(['parent', 'systems'])->orderBy('sort_order')->orderBy('name')->get();

        return response()->json(StructureResource::collection($structures)->resolve());
    }

    public function detachFromProject(Project $project, Structure $structure): JsonResponse
    {
        $project->structures()->detach($structure->id);

        return response()->json(null, 204);
    }

    public function store(StoreStructureRequest $request): JsonResponse
    {
        $structure = Structure::create($request->validated());

        return response()->json((new StructureResource($structure->load(['parent', 'systems'])))->resolve(), 201);
    }

    public function show(Structure $structure): JsonResponse
    {
        return response()->json((new StructureResource($structure->load(['farm', 'parent', 'systems'])))->resolve());
    }

    public function update(UpdateStructureRequest $request, Structure $structure): JsonResponse
    {
        $structure->update($request->validated());

        return response()->json((new StructureResource($structure->load(['parent', 'systems'])))->resolve());
    }

    public function destroy(Structure $structure): JsonResponse
    {
        $structure->delete();

        return response()->json(null, 204);
    }
}
