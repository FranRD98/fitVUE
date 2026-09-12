<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    private const EQUIPMENT_OPTIONS = [
        'ninguno', 'banda_resistencia', 'banda_suspension', 'barra',
        'disco', 'mancuerna', 'maquina', 'pesa_rusa', 'otro',
    ];

    public function index(Request $request)
    {
        $userId = $request->query('user_id', $request->user()->id);
        $adminIds = User::where('role', 'admin')->pluck('id');

        $exercises = Exercise::with(['category', 'secondaryMuscles'])
            ->whereIn('created_by', [$userId, ...$adminIds])
            ->orderBy('name')
            ->get()
            ->map(fn (Exercise $exercise) => $this->withCategory($exercise));

        return response()->json($exercises);
    }

    public function show(Exercise $exercise)
    {
        return response()->json($this->withCategory($exercise->load(['category', 'secondaryMuscles'])));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'id_category' => ['required', 'integer', 'exists:exercises_categories,id'],
            'equipment' => ['required', 'string', 'in:'.implode(',', self::EQUIPMENT_OPTIONS)],
            'image' => ['nullable', 'string'],
            'secondary_muscle_ids' => ['sometimes', 'array'],
            'secondary_muscle_ids.*' => ['integer', 'exists:exercises_categories,id'],
        ]);

        $secondaryMuscleIds = $data['secondary_muscle_ids'] ?? [];
        unset($data['secondary_muscle_ids']);

        $data['created_by'] = $request->user()->id;

        $exercise = Exercise::create($data);
        $exercise->secondaryMuscles()->sync($secondaryMuscleIds);

        return response()->json($this->withCategory($exercise->load(['category', 'secondaryMuscles'])), 201);
    }

    public function update(Request $request, Exercise $exercise)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'id_category' => ['sometimes', 'integer', 'exists:exercises_categories,id'],
            'equipment' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', self::EQUIPMENT_OPTIONS)],
            'image' => ['sometimes', 'nullable', 'string'],
            'image_url' => ['sometimes', 'nullable', 'string'],
            'secondary_muscle_ids' => ['sometimes', 'array'],
            'secondary_muscle_ids.*' => ['integer', 'exists:exercises_categories,id'],
        ]);

        if (array_key_exists('image_url', $data)) {
            $data['image'] = $data['image_url'];
            unset($data['image_url']);
        }

        if (array_key_exists('secondary_muscle_ids', $data)) {
            $exercise->secondaryMuscles()->sync($data['secondary_muscle_ids']);
            unset($data['secondary_muscle_ids']);
        }

        $exercise->update($data);

        return response()->json($this->withCategory($exercise->fresh(['category', 'secondaryMuscles'])));
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
