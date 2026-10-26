<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Diet;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DietController extends Controller
{
    public function index(Request $request)
    {
        $hasCatalog = Schema::hasColumn('diets', 'is_catalog');

        if ($request->boolean('catalog')) {
            if ($request->user()->role !== 'admin') {
                abort(403);
            }

            if (! $hasCatalog) {
                return response()->json([]);
            }

            return response()->json(Diet::where('is_catalog', true)->orderBy('title')->get());
        }

        $userId = $request->query('user_id', $request->user()->id);
        $query = Diet::where('user_id', $userId)->orderBy('title');

        if ($hasCatalog) {
            $query->where('is_catalog', false);
        }

        return response()->json($query->get());
    }

    public function coachAssigned(User $user)
    {
        return response()->json($user->assignedDiet);
    }

    public function full(Diet $diet)
    {
        return response()->json($this->hydrateIngredients($diet));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $hasCatalog = Schema::hasColumn('diets', 'is_catalog');

        if ($hasCatalog && $request->boolean('is_catalog')) {
            if ($request->user()->role !== 'admin') {
                abort(403);
            }

            $data['is_catalog'] = true;
            $data['published'] = $request->boolean('published');
            $data['user_id'] = $request->user()->id;
        } else {
            unset($data['is_catalog'], $data['published']);
            if ($hasCatalog) {
                $data['is_catalog'] = false;
                $data['published'] = false;
            }
        }

        return response()->json(Diet::create($data), 201);
    }

    public function update(Request $request, Diet $diet)
    {
        $hasCatalog = Schema::hasColumn('diets', 'is_catalog');

        if ($hasCatalog && $diet->is_catalog && $request->user()->role !== 'admin') {
            abort(403);
        }

        $data = $this->validated($request, sometimes: true);

        if (! $hasCatalog || ! $diet->is_catalog) {
            unset($data['published'], $data['is_catalog']);
        }

        $diet->update($data);

        return response()->json($diet->fresh());
    }

    public function destroy(Diet $diet)
    {
        $diet->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        return $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'user_id' => $sometimes ? ['sometimes', 'nullable', 'integer', 'exists:users,id'] : ['required', 'nullable', 'integer', 'exists:users,id'],
            'meals' => ['sometimes', 'array'],
            'is_catalog' => ['sometimes', 'boolean'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }

    private function hydrateIngredients(Diet $diet): array
    {
        $diet = $diet->toArray();
        $ingredientIds = collect($diet['meals'] ?? [])
            ->flatMap(fn ($meal) => $meal['items'] ?? [])
            ->flatMap(fn ($plate) => $plate['items'] ?? [])
            ->pluck('ingredient_id')
            ->filter()
            ->unique();

        $ingredients = Ingredient::whereIn('id', $ingredientIds)->get()->keyBy('id');

        $diet['meals'] = collect($diet['meals'] ?? [])->map(function ($meal) use ($ingredients) {
            $meal['items'] = collect($meal['items'] ?? [])->map(function ($plate) use ($ingredients) {
                $plate['items'] = collect($plate['items'] ?? [])->map(function ($item) use ($ingredients) {
                    $item['ingredient'] = $ingredients->get($item['ingredient_id'] ?? null);

                    return $item;
                })->all();

                return $plate;
            })->all();

            return $meal;
        })->all();

        return $diet;
    }
}
