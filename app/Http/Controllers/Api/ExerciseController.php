<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id', $request->user()->id);
        $adminIds = User::where('role', 'admin')->pluck('id');

        $exercises = Exercise::with('category')
            ->whereIn('created_by', [$userId, ...$adminIds])
            ->orderBy('name')
            ->get()
            ->map(fn (Exercise $exercise) => $this->withCategory($exercise));

        return response()->json($exercises);
    }

    public function show(Exercise $exercise)
    {
        return response()->json($exercise);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'id_category' => ['required', 'integer', 'exists:exercises_categories,id'],
            'image' => ['nullable', 'string'],
        ]);

        $data['created_by'] = $request->user()->id;

        return response()->json(Exercise::create($data), 201);
    }

    public function update(Request $request, Exercise $exercise)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'id_category' => ['sometimes', 'integer', 'exists:exercises_categories,id'],
            'image' => ['sometimes', 'nullable', 'string'],
            'image_url' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('image_url', $data)) {
            $data['image'] = $data['image_url'];
            unset($data['image_url']);
        }

        $exercise->update($data);

        return response()->json($exercise->fresh());
    }

    public function destroy(Exercise $exercise)
    {
        $exercise->delete();

        return response()->json(['success' => true]);
    }

    private function withCategory(Exercise $exercise): array
    {
        $array = $exercise->toArray();
        $array['exercises_categories'] = ['category_name' => $exercise->category?->category_name];

        return $array;
    }
}
