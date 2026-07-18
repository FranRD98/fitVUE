<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\RoutineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoutineCategoryController extends Controller
{
    public function index()
    {
        return response()->json(RoutineCategory::all());
    }

    public function inUse()
    {
        $categoryIds = Routine::where('published', true)->pluck('id_category')->unique()->filter()->values();

        $categories = RoutineCategory::whereIn('id', $categoryIds)->get()->map(function (RoutineCategory $category) {
            $category->icon_path = $category->icon_path
                ? Storage::disk('public')->url($category->icon_path)
                : '/icons/default-icon.svg';

            return $category;
        });

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);

        $existing = RoutineCategory::whereRaw('LOWER(title) = ?', [mb_strtolower($data['title'])])->first();

        if ($existing) {
            return response()->json($existing);
        }

        return response()->json(RoutineCategory::create($data), 201);
    }
}
