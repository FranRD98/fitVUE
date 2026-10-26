<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
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

        $byDate = [];
        foreach ($this->buildSessions($data['user_id']) as $session) {
            $date = $session['date'];
            $byDate[$date] ??= ['date' => $date, 'routines' => [], 'sessions' => []];

            if (! in_array($session['routine_title'], $byDate[$date]['routines'], true)) {
                $byDate[$date]['routines'][] = $session['routine_title'];
            }

            $byDate[$date]['sessions'][] = $session;
        }

        return response()->json(array_values($byDate));
    }

    public function sessions(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer']]);

        $sessions = $this->buildSessions($data['user_id'])
            ->sortByDesc('created_at')
            ->values();

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'id_routine' => ['nullable', 'integer', 'exists:routines,id'],
            'day' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'exercises' => ['required', 'array', 'min:1'],
            'exercises.*.exerciseId' => ['required', 'integer'],
            'exercises.*.name' => ['nullable', 'string'],
            'exercises.*.sets' => ['required', 'array', 'min:1'],
        ]);

        // Las rutinas guardan los ejercicios como JSON suelto, sin relación real
        // (ver Routine::$fillable). Si el ejercicio original se borró del catálogo
        // después de crear la rutina, la referencia queda huérfana: se descarta
        // aquí en vez de tumbar el guardado completo de la sesión.
        $existingIds = Exercise::whereIn('id', collect($data['exercises'])->pluck('exerciseId'))
            ->pluck('id')
            ->all();

        $now = now();

        $entries = collect($data['exercises'])
            ->filter(fn ($exercise) => in_array($exercise['exerciseId'], $existingIds, true))
            ->map(fn ($exercise) => [
                'user_id' => $data['user_id'],
                'id_routine' => $data['id_routine'] ?? null,
                'exercise_id' => $exercise['exerciseId'],
                'exercise_name' => $exercise['name'] ?? null,
                'day' => $data['day'] ?? null,
                'sets' => json_encode($exercise['sets']),
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'created_at' => $now,
            ])
            ->all();

        if (empty($entries)) {
            return response()->json([
                'message' => 'Ninguno de los ejercicios de esta sesión existe ya en el catálogo. Revisa la rutina: puede tener un ejercicio borrado.',
            ], 422);
        }

        ExerciseProgress::insert($entries);

        return response()->json(['success' => true], 201);
    }

    // Agrupa las filas de un mismo guardado (misma rutina y mismo instante)
    // en una sesión, con los ejercicios y las series tal cual se registraron.
    private function buildSessions(int $userId)
    {
        $rows = ExerciseProgress::where('user_id', $userId)
            ->with('routine:id,title')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'id_routine', 'exercise_name', 'sets', 'duration_seconds', 'created_at']);

        $sessions = [];
        foreach ($rows as $row) {
            $key = $row->created_at->format('Y-m-d H:i:s').'|'.$row->id_routine;

            $sessions[$key] ??= [
                'created_at' => $row->created_at->toIso8601String(),
                'date' => $row->created_at->format('Y-m-d'),
                'routine_title' => $row->routine->title ?? 'Entrenamiento',
                'exercise_count' => 0,
                'set_count' => 0,
                'duration_seconds' => $row->duration_seconds,
                'exercises' => [],
            ];

            $sets = collect(is_array($row->sets) ? $row->sets : [])
                ->map(fn ($set) => [
                    'reps' => $set['reps'] ?? null,
                    'weight' => $set['weight'] ?? null,
                ])
                ->values()
                ->all();

            $sessions[$key]['exercises'][] = [
                'name' => $row->exercise_name ?: 'Ejercicio',
                'sets' => $sets,
            ];
            $sessions[$key]['exercise_count']++;
            $sessions[$key]['set_count'] += count($sets);
        }

        return collect($sessions)->values();
    }
}
