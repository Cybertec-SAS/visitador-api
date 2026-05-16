<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGalponRequest;
use App\Http\Requests\UpdateGalponRequest;
use App\Http\Resources\GalponResource;
use App\Models\Farm;
use App\Models\Galpon;
use Illuminate\Http\JsonResponse;

class GalponController extends Controller
{
    public function index(Farm $farm): JsonResponse
    {
        $galpones = $farm->galpones()->with('systems.system')->get();

        return GalponResource::collection($galpones)->response();
    }

    public function store(StoreGalponRequest $request, Farm $farm): JsonResponse
    {
        $galpon = $farm->galpones()->create($request->validated());

        return (new GalponResource($galpon->load('systems.system')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Galpon $galpon): GalponResource
    {
        $galpon->load('systems.system');

        return new GalponResource($galpon);
    }

    public function update(UpdateGalponRequest $request, Galpon $galpon): GalponResource
    {
        $galpon->update($request->validated());

        return new GalponResource($galpon->load('systems.system'));
    }

    public function destroy(Galpon $galpon): JsonResponse
    {
        $galpon->delete();

        return response()->json(null, 204);
    }
}
