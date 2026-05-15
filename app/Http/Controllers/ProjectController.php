<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['client', 'farm'])
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->farm_id, fn($q) => $q->where('farm_id', $request->farm_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,active,paused,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        return response()->json(Project::create($validated)->load(['client', 'farm']), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load(['client', 'farm', 'progressReports']));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,active,paused,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project->load(['client', 'farm']));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
