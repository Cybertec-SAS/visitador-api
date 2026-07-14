<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitPhotoController extends Controller
{
    public function store(Request $request, Visit $visit): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'max:8192'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('file');
        $name = Str::uuid().'.'.($file->getClientOriginalExtension() ?: $file->extension());

        $file->storeAs($this->directory($visit), $name, $this->diskName());

        return response()->json([
            'id' => $name,
            'url' => $this->fotoUrl($visit, $name),
            'descripcion' => $validated['descripcion'] ?? null,
        ], 201);
    }

    public function show(Visit $visit, string $photo): StreamedResponse
    {
        $path = $this->directory($visit).'/'.$photo;

        abort_unless($this->disk()->exists($path), 404);

        return $this->disk()->response($path);
    }

    public function destroy(Visit $visit, string $photo): JsonResponse
    {
        $this->disk()->delete($this->directory($visit).'/'.$photo);

        return response()->json(null, 204);
    }

    private function diskName(): string
    {
        return config('filesystems.visits_disk');
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    private function directory(Visit $visit): string
    {
        return "visits/{$visit->id}";
    }

    /**
     * Portable evidence URL: a signed temporary URL when the disk supports it
     * (S3 / Supabase), otherwise the authenticated streaming route (local).
     */
    private function fotoUrl(Visit $visit, string $photo): string
    {
        $disk = $this->disk();
        $path = $this->directory($visit).'/'.$photo;

        if (method_exists($disk, 'providesTemporaryUrls') && $disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl($path, now()->addHour());
        }

        return route('visits.fotos.show', ['visit' => $visit->id, 'photo' => $photo]);
    }
}
