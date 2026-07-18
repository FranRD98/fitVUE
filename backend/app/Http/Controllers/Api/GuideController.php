<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index()
    {
        return response()->json(Guide::orderByDesc('created_at')->get());
    }

    public function published()
    {
        return response()->json(Guide::where('published', true)->orderByDesc('created_at')->get());
    }

    public function show(Guide $guide)
    {
        return response()->json($guide);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['author'] ??= trim($request->user()->name.' '.$request->user()->last_name);

        return response()->json(Guide::create($data), 201);
    }

    public function update(Request $request, Guide $guide)
    {
        $guide->update($this->validated($request, sometimes: true));

        return response()->json($guide->fresh());
    }

    public function destroy(Guide $guide)
    {
        $guide->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        $rule = $sometimes ? ['sometimes'] : ['required'];

        return $request->validate([
            'title' => [...$rule, 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'author' => ['sometimes', 'nullable', 'string'],
            'id_category' => [...$rule, 'integer', 'exists:guides_categories,id'],
            'header_image' => ['sometimes', 'nullable', 'string'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }
}
