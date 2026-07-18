<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    private array $measurements = [
        'weight', 'neck', 'shoulders', 'chest', 'biceps_relaxed', 'biceps_flexed',
        'forearm', 'wrist', 'waist', 'abdomen', 'hips', 'quadriceps', 'calves',
    ];

    public function index(Request $request)
    {
        $userId = $request->query('user_id', $request->user()->id);

        return response()->json(Progress::where('user_id', $userId)->orderByDesc('created_at')->get());
    }

    public function show(Request $request, Progress $progress)
    {
        $userId = $request->query('user_id', $request->user()->id);

        if ($progress->user_id !== (int) $userId) {
            abort(404);
        }

        return response()->json($progress);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            ...array_fill_keys($this->measurements, ['nullable', 'numeric']),
        ]);

        $data['created_at'] = now();

        return response()->json(Progress::create($data), 201);
    }

    public function update(Request $request, Progress $progress)
    {
        $data = $request->validate(array_fill_keys($this->measurements, ['sometimes', 'nullable', 'numeric']));

        $progress->update($data);

        return response()->json($progress->fresh());
    }

    public function destroy(Progress $progress)
    {
        $progress->delete();

        return response()->json(['success' => true]);
    }
}
