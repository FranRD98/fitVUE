<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        $query = Routine::query()->orderBy('title');

        if ($request->filled('category')) {
            $query->where('id_category', $request->query('category'));
        }

        return response()->json($query->get());
    }

    public function published()
    {
        return response()->json(Routine::where('published', true)->orderBy('title')->get());
    }

    public function byUser(User $user)
    {
        return response()->json(Routine::where('user_id', $user->id)->get());
    }

    public function show(Routine $routine)
    {
        return response()->json($routine);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;

        return response()->json(Routine::create($data), 201);
    }

    public function update(Request $request, Routine $routine)
    {
        $routine->update($this->validated($request, sometimes: true));

        return response()->json($routine->fresh());
    }

    public function destroy(Routine $routine)
    {
        $routine->delete();

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, User $user)
    {
        $data = $request->validate(['routine_id' => ['required', 'integer', 'exists:routines,id']]);
        $user->update(['assigned_routine' => $data['routine_id']]);

        return response()->json(['success' => true]);
    }

    public function unassign(User $user)
    {
        $user->update(['assigned_routine' => null]);

        return response()->json(['success' => true]);
    }

    public function assigned(User $user)
    {
        return response()->json($user->assignedRoutine);
    }

    public function coachAssigned(User $user)
    {
        return response()->json($user->assignedRoutineByCoach);
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        return $request->validate([
            'title' => $sometimes ? ['sometimes', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'id_category' => $sometimes ? ['sometimes', 'integer', 'exists:routines_categories,id'] : ['required', 'integer', 'exists:routines_categories,id'],
            'days' => ['sometimes', 'array'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }
}
