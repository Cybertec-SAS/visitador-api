<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGalponSystemRequest;
use App\Http\Requests\UpdateGalponSystemRequest;
use App\Http\Resources\GalponSystemResource;
use App\Models\Galpon;
use App\Models\GalponSystem;
use Illuminate\Http\JsonResponse;

class GalponSystemController extends Controller
{
    public function index(Galpon $galpon): JsonResponse
    {
        $systems = $galpon->systems()->with('system')->get();

        return GalponSystemResource::collection($systems)->response();
    }

    public function store(StoreGalponSystemRequest $request, Galpon $galpon): JsonResponse
    {
        $galponSystem = $galpon->systems()->create($request->validated());

        return (new GalponSystemResource($galponSystem->load('system')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(GalponSystem $galponSystem): GalponSystemResource
    {
        $galponSystem->load('system');

        return new GalponSystemResource($galponSystem);
    }

    public function update(UpdateGalponSystemRequest $request, GalponSystem $galponSystem): GalponSystemResource
    {
        $galponSystem->update($request->validated());

        return new GalponSystemResource($galponSystem->load('system'));
    }

    public function destroy(GalponSystem $galponSystem): JsonResponse
    {
        $galponSystem->delete();

        return response()->json(null, 204);
    }
}