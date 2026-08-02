<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    private const ALLOWED_PREFIXES = ['icons/exercises/', 'icons/guides/'];

    public function exercises(Request $request)
    {
        return $this->store($request, 'icons/exercises');
    }

    public function guides(Request $request)
    {
        return $this->store($request, 'icons/guides');
    }

    private function store(Request $request, string $folder)
    {
        $request->validate(['image' => ['required', 'image', 'max:4096']]);

        $path = $request->file('image')->store($folder, 'public');

        return response()->json(['url' => Storage::disk('public')->url($path), 'path' => $path]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['path' => ['required', 'string']]);

        $isAllowed = collect(self::ALLOWED_PREFIXES)->contains(fn ($prefix) => str_starts_with($data['path'], $prefix));

        if (! $isAllowed) {
            abort(422, 'Ruta no permitida.');
        }

        Storage::disk('public')->delete($data['path']);

        return response()->json(['success' => true]);
    }
}
