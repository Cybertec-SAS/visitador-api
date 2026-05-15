<?php

namespace App\Http\Controllers;

use App\Models\SystemsCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemsCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SystemsCatalog::where('is_active', true)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedPayload($request, false);

        return response()->json(SystemsCatalog::create($validated), 201);
    }

    public function show(SystemsCatalog $systemsCatalog): JsonResponse
    {
        return response()->json($systemsCatalog);
    }

    public function update(Request $request, SystemsCatalog $systemsCatalog): JsonResponse
    {
        $validated = $this->validatedPayload($request, true);

        $systemsCatalog->update($validated);

        return response()->json($systemsCatalog);
    }

    public function destroy(SystemsCatalog $systemsCatalog): JsonResponse
    {
        $systemsCatalog->delete();

        return response()->json(null, 204);
    }

    private function validatedPayload(Request $request, bool $isUpdate): array
    {
        $payload = $request->all();

        foreach (['code', 'name', 'category'] as $field) {
            if (! array_key_exists($field, $payload) || ! is_string($payload[$field])) {
                continue;
            }

            $payload[$field] = mb_strtoupper($payload[$field], 'UTF-8');
        }

        return Validator::make($payload, $isUpdate ? [
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ] : [
            'code' => 'required|string|unique:systems_catalog,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ])->validate();
    }
}
