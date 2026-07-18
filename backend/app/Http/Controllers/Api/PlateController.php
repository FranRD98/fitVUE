<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Plate;
use Illuminate\Http\Request;

class PlateController extends Controller
{
    public function index(Request $request)
    {
        $query = Plate::query()->select('id', 'name', 'items', 'created_by')->orderBy('name');

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->query('created_by'));
        }

        $plates = $query->get()->map(function (Plate $plate) {
            $plate = $plate->toArray();
            $ingredientIds = collect($plate['items'] ?? [])->pluck('ingredient_id')->filter();
            $ingredients = Ingredient::whereIn('id', $ingredientIds)->orderBy('name')->get()->keyBy('id');

            $plate['items'] = collect($plate['items'] ?? [])->map(function ($item) use ($ingredients) {
                $item['ingredient'] = $ingredients->get($item['ingredient_id'] ?? null);

                return $item;
            })->all();

            return $plate;
        });

        return response()->json($plates);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['sometimes', 'array'],
        ]);

        $data['created_by'] = $request->user()->id;

        return response()->json(Plate::create($data), 201);
    }

    public function update(Request $request, Plate $plate)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'items' => ['sometimes', 'array'],
        ]);

        $plate->update($data);

        return response()->json($plate->fresh());
    }

    public function destroy(Plate $plate)
    {
        $plate->delete();

        return response()->json(['success' => true]);
    }
}
