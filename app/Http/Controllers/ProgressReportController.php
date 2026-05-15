<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json(
            $project->progressReports()
                ->orderBy('report_number')
                ->get()
        );
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'report_number' => 'required|integer|min:1',
            'cutoff_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'weighted_progress_percent' => 'nullable|numeric|min:0|max:100',
            'scheduled_progress_percent' => 'nullable|numeric|min:0|max:100',
            'difference_percent' => 'nullable|numeric',
            'contract_days' => 'nullable|integer',
            'elapsed_days' => 'nullable|integer',
            'remaining_days' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        return response()->json($project->progressReports()->create($validated)->load('project'), 201);
    }

    public function show(ProgressReport $progressReport): JsonResponse
    {
        return response()->json($progressReport->load(['project', 'items', 'curvePoints']));
    }

    public function update(Request $request, ProgressReport $progressReport): JsonResponse
    {
        $validated = $request->validate([
            'cutoff_date' => 'sometimes|date',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'weighted_progress_percent' => 'nullable|numeric|min:0|max:100',
            'scheduled_progress_percent' => 'nullable|numeric|min:0|max:100',
            'difference_percent' => 'nullable|numeric',
            'contract_days' => 'nullable|integer',
            'elapsed_days' => 'nullable|integer',
            'remaining_days' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $progressReport->update($validated);

        return response()->json($progressReport->load('project'));
    }

    public function destroy(ProgressReport $progressReport): JsonResponse
    {
        $progressReport->delete();

        return response()->json(null, 204);
    }
}
