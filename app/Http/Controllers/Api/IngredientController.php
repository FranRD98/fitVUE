<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Plate;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('ids')) {
            $ids = explode(',', $request->query('ids'));

            return response()->json(Ingredient::whereIn('id', $ids)->orderBy('name')->get());
        }

        $userId = $request->query('user_id', $request->user()->id);

        return response()->json(Ingredient::where('created_by', $userId)->orderBy('name')->get());
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json($ingredient);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        return response()->json(Ingredient::create($data), 201);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $ingredient->update($this->validated($request, sometimes: true));

        return response()->json($ingredient->fresh());
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json(['success' => true]);
    }

    public function used(Ingredient $ingredient)
    {
        $used = Plate::query()->select('items')->get()
            ->contains(fn (Plate $plate) => collect($plate->items)->contains('ingredient_id', $ingredient->id));

        return response()->json(['used' => $used]);
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        $rule = $sometimes ? ['sometimes'] : ['required'];

        return $request->validate([
            'name' => [...$rule, 'string', 'max:255'],
            'calories' => ['sometimes', 'numeric'],
            'protein' => ['sometimes', 'numeric'],
            'carbs' => ['sometimes', 'numeric'],
            'fats' => ['sometimes', 'numeric'],
        ]);
    }
}
