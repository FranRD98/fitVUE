<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseRequest;
use Illuminate\Http\Request;

class ExerciseRequestController extends Controller
{
    private const EQUIPMENT_OPTIONS = [
        'ninguno', 'banda_resistencia', 'banda_suspension', 'barra',
        'disco', 'mancuerna', 'maquina', 'pesa_rusa', 'otro',
    ];

    // Solo el admin ve el listado de solicitudes pendientes de todos los usuarios.
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json(
            ExerciseRequest::with('user:id,name,last_name')
                ->orderByRaw("status = 'pending' desc")
                ->latest()
                ->get()
        );
    }

    // Cualquier usuario autenticado puede solicitar un ejercicio que no existe en el catálogo.
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $exerciseRequest = ExerciseRequest::create($data);

        return response()->json($exerciseRequest, 201);
    }

    // El admin aprueba la solicitud: se da de alta el ejercicio real en el catálogo.
    public function approve(Request $request, ExerciseRequest $exerciseRequest)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'id_category' => ['required', 'integer', 'exists:exercises_categories,id'],
            'equipment' => ['required', 'string', 'in:'.implode(',', self::EQUIPMENT_OPTIONS)],
            'image' => ['nullable', 'string'],
        ]);

        $exercise = Exercise::create([
            'name' => $exerciseRequest->name,
            'description' => $exerciseRequest->description,
            'id_category' => $data['id_category'],
            'equipment' => $data['equipment'],
            'image' => $data['image'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $exerciseRequest->update(['status' => 'approved', 'exercise_id' => $exercise->id]);

        return response()->json($exercise->load('category'), 201);
    }

    public function reject(Request $request, ExerciseRequest $exerciseRequest)
    {
        $this->authorizeAdmin($request);

        $exerciseRequest->update(['status' => 'rejected']);

        return response()->json(['success' => true]);
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Solo un administrador puede gestionar solicitudes de ejercicios.');
        }
    }
}
