<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseProgress;
use Illuminate\Http\Request;

class ExerciseProgressController extends Controller
{
    public function last(Request $request)
    {
        $data = $request->validate([
            'exercise_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
        ]);

        $progress = ExerciseProgress::where('exercise_id', $data['exercise_id'])
            ->where('user_id', $data['user_id'])
            ->orderByDesc('created_at')
            ->select('sets', 'created_at')
            ->first();

        return response()->json($progress);
    }

    public function history(Request $request)
    {
        $data = $request->validate([
            'exercise_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
        ]);

        $history = ExerciseProgress::where('exercise_id', $data['exercise_id'])
            ->where('user_id', $data['user_id'])
            ->orderByDesc('created_at')
            ->select('created_at', 'sets')
            ->get();

        return response()->json($history);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'id_routine' => ['nullable', 'integer', 'exists:routines,id'],
            'day' => ['nullable', 'string'],
            'exercises' => ['required', 'array', 'min:1'],
            'exercises.*.exerciseId' => ['required', 'integer', 'exists:exercises,id'],
            'exercises.*.name' => ['nullable', 'string'],
            'exercises.*.sets' => ['required', 'array', 'min:1'],
        ]);

        $now = now();

        $entries = collect($data['exercises'])->map(fn ($exercise) => [
            'user_id' => $data['user_id'],
            'id_routine' => $data['id_routine'] ?? null,
            'exercise_id' => $exercise['exerciseId'],
            'exercise_name' => $exercise['name'] ?? null,
            'day' => $data['day'] ?? null,
            'sets' => json_encode($exercise['sets']),
            'created_at' => $now,
        ])->all();

        ExerciseProgress::insert($entries);

        return response()->json(['success' => true], 201);
    }
}
