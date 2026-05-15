<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;

class FarmController extends Controller
{
    public function index(): JsonResponse
    {
        $farms = Farm::with(['client', 'georreference', 'contacts'])->paginate(15);

        return FarmResource::collection($farms)->response();
    }

    public function store(StoreFarmRequest $request): JsonResponse
    {
        $farm = Farm::create($request->validated());

        return (new FarmResource($farm->load(['client', 'georreference', 'contacts'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Farm $farm): FarmResource
    {
        $farm->load(['client', 'georreference', 'contacts']);

        return new FarmResource($farm);
    }

    public function update(UpdateFarmRequest $request, Farm $farm): FarmResource
    {
        $farm->update($request->validated());

        return new FarmResource($farm->load(['client', 'georreference', 'contacts']));
    }

    public function destroy(Farm $farm): JsonResponse
    {
        $farm->delete();

        return response()->json(null, 204);
    }
}
