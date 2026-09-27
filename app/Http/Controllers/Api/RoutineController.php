<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\RoutineCategory;
use App\Models\User;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    // Listado completo de rutinas de todos los usuarios: solo para el panel de admin,
    // que necesita ver a quién tiene asignada cada rutina y poder filtrar por usuario.
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $query = Routine::with('user:id,name,last_name,email')->orderBy('title');

        if ($request->filled('category')) {
            $query->where('id_category', $request->query('category'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
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
        $user = $request->user();

        // Plan gratuito: máximo 3 rutinas propias. Se valida aquí además de en el
        // frontend para que no se pueda saltar el límite llamando a la API directamente.
        // Los coaches y admins tienen siempre permisos de plan Pro.
        if (! $user->isCoachOrAdmin() && $user->plan_id === 1 && Routine::where('user_id', $user->id)->count() >= 3) {
            return response()->json([
                'message' => 'Has alcanzado el límite de 3 rutinas del plan gratuito. Actualiza tu plan para crear más.',
                'limit_reached' => true,
            ], 403);
        }

        $data = $this->validated($request);
        $data['user_id'] = $user->id;
        $data['id_category'] ??= $this->defaultCategoryId();

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

    public function duplicate(Request $request, Routine $routine)
    {
        $copy = $routine->replicate();
        $copy->title = $routine->title.' (copia)';
        $copy->user_id = $request->user()->id;
        $copy->published = false;
        $copy->save();

        return response()->json($copy, 201);
    }

    // Envía una copia propia de la rutina a un usuario: a diferencia de assign()
    // (que solo enlaza a la rutina original de forma de solo lectura), aquí el
    // usuario recibe su propia rutina independiente, editable y borrable por él.
    public function sendToUser(Request $request, User $user)
    {
        $sender = $request->user();

        if (! in_array($sender->role, ['coach', 'admin'], true)) {
            abort(403);
        }

        if ($sender->role === 'coach' && $user->coach_uid !== $sender->id) {
            abort(403);
        }

        $data = $request->validate(['routine_id' => ['required', 'integer', 'exists:routines,id']]);

        $routine = Routine::findOrFail($data['routine_id']);

        if ($routine->user_id !== $sender->id) {
            abort(403, 'Solo puedes enviar rutinas que hayas creado tú.');
        }

        $copy = $routine->replicate();
        $copy->user_id = $user->id;
        $copy->published = false;
        $copy->save();

        return response()->json($copy, 201);
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
            'id_category' => ['sometimes', 'nullable', 'integer', 'exists:routines_categories,id'],
            'exercises' => ['sometimes', 'array'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }

    // De momento solo existe el tipo "Entrenamientos": si el frontend no manda
    // id_category, la rutina se crea con esta categoría por defecto.
    private function defaultCategoryId(): ?int
    {
        return RoutineCategory::firstOrCreate(['title' => 'Entrenamientos'])->id;
    }
}
