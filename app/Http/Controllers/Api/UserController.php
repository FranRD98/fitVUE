<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user();

        $query = User::query();

        if ($me->role === 'coach') {
            $query->where('coach_uid', $me->id);
        } elseif ($me->role !== 'admin') {
            abort(403);
        }

        return response()->json($query->get());
    }

    public function coaches()
    {
        return response()->json(
            User::where('role', 'coach')->select('id', 'name', 'last_name', 'email')->get()
        );
    }

    // Creación de un cliente/usuario por parte de un coach o admin.
    public function store(Request $request)
    {
        $me = $request->user();

        if (! in_array($me->role, ['coach', 'admin'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(['user', 'coach', 'admin'])],
            'plan_id' => ['sometimes', 'integer'],
            'coach_uid' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'assigned_routine' => ['sometimes', 'nullable', 'integer', 'exists:routines,id'],
            'assigned_routine_by_coach' => ['sometimes', 'nullable', 'integer', 'exists:routines,id'],
            'assigned_diet' => ['sometimes', 'nullable', 'integer', 'exists:diets,id'],
        ]);

        if ($me->role === 'coach') {
            $data['role'] = 'user';
            $data['coach_uid'] = $me->id;
        }

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $me = $request->user();

        if ($me->role === 'coach' && $user->coach_uid !== $me->id) {
            abort(403);
        } elseif (! in_array($me->role, ['coach', 'admin'], true) && $me->id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(['user', 'coach', 'admin'])],
            'plan_id' => ['sometimes', 'integer'],
            'coach_uid' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'assigned_routine' => ['sometimes', 'nullable', 'integer', 'exists:routines,id'],
            'assigned_routine_by_coach' => ['sometimes', 'nullable', 'integer', 'exists:routines,id'],
            'assigned_diet' => ['sometimes', 'nullable', 'integer', 'exists:diets,id'],
            'profile_image' => ['sometimes', 'nullable', 'string'],
            'completed_form' => ['sometimes', 'boolean'],
            'birthday' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:20'],
            'goal' => ['sometimes', 'nullable', 'string', 'max:50'],
            'height' => ['sometimes', 'nullable', 'numeric'],
            'weight' => ['sometimes', 'nullable', 'numeric'],
            'age' => ['sometimes', 'nullable', 'integer'],
            'activity' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        // Solo el admin puede cambiar rol/plan/coach asignado.
        if ($me->role !== 'admin') {
            unset($data['role'], $data['plan_id'], $data['coach_uid']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(Request $request, User $user)
    {
        $me = $request->user();

        if (! in_array($me->role, ['coach', 'admin'], true)) {
            abort(403);
        }

        if ($me->role === 'coach' && $user->coach_uid !== $me->id) {
            abort(403);
        }

        // Las cuentas admin no se pueden borrar desde el panel (ni saltándose la UI).
        if ($user->role === 'admin') {
            abort(403, 'No se puede eliminar una cuenta de administrador.');
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    public function uploadProfileImage(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            abort(403);
        }

        $request->validate(['image' => ['required', 'image', 'max:4096']]);

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('image')->store('icons/profile-images', 'public');
        $user->update(['profile_image' => $path]);

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }
}
