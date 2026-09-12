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

    public function stats(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer']]);

        $rows = ExerciseProgress::where('user_id', $data['user_id'])
            ->orderBy('created_at')
            ->get(['exercise_id', 'exercise_name', 'sets', 'created_at']);

        $best = [];
        foreach ($rows as $row) {
            $maxWeight = 0;
            foreach (is_array($row->sets) ? $row->sets : [] as $set) {
                $weight = (float) ($set['weight'] ?? 0);
                if ($weight > $maxWeight) {
                    $maxWeight = $weight;
                }
            }

            if ($maxWeight <= 0) {
                continue;
            }

            $existing = $best[$row->exercise_id] ?? null;
            if (! $existing || $maxWeight > $existing['max_weight']) {
                $best[$row->exercise_id] = [
                    'exercise_id' => $row->exercise_id,
                    'exercise_name' => $row->exercise_name,
                    'max_weight' => $maxWeight,
                    'achieved_at' => $row->created_at,
                ];
            }
        }

        return response()->json(collect($best)->values()->sortBy('exercise_name')->values());
    }

    public function calendar(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer']]);

        $rows = ExerciseProgress::where('user_id', $data['user_id'])
            ->with('routine:id,title')
            ->orderBy('created_at')
            ->get(['id_routine', 'created_at']);

        $byDate = [];
        foreach ($rows as $row) {
            $date = $row->created_at->format('Y-m-d');
            $byDate[$date] ??= ['date' => $date, 'routines' => []];

            $title = $row->routine->title ?? 'Entrenamiento';
            if (! in_array($title, $byDate[$date]['routines'], true)) {
                $byDate[$date]['routines'][] = $title;
            }
        }

        return response()->json(array_values($byDate));
    }

    public function sessions(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer']]);

        $rows = ExerciseProgress::where('user_id', $data['user_id'])
            ->with('routine:id,title')
            ->orderByDesc('created_at')
            ->get(['id_routine', 'sets', 'duration_seconds', 'created_at']);

        $sessions = [];
        foreach ($rows as $row) {
            $key = $row->created_at->toDateTimeString().'|'.$row->id_routine;

            $sessions[$key] ??= [
                'created_at' => $row->created_at,
                'routine_title' => $row->routine->title ?? 'Entrenamiento',
                'exercise_count' => 0,
                'set_count' => 0,
                'duration_seconds' => $row->duration_seconds,
            ];

            $sessions[$key]['exercise_count']++;
            $sessions[$key]['set_count'] += is_array($row->sets) ? count($row->sets) : 0;
        }

        return response()->json(collect($sessions)->values()->sortByDesc('created_at')->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'id_routine' => ['nullable', 'integer', 'exists:routines,id'],
            'day' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
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
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'created_at' => $now,
        ])->all();

        ExerciseProgress::insert($entries);

        return response()->json(['success' => true], 201);
    }
}
