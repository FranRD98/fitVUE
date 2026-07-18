<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\GuideCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuideCategoryController extends Controller
{
    public function index()
    {
        return response()->json(GuideCategory::all());
    }

    public function inUse()
    {
        $categoryIds = Guide::where('published', true)->pluck('id_category')->unique()->filter()->values();

        $categories = GuideCategory::whereIn('id', $categoryIds)->get()->map(function (GuideCategory $category) {
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

        return response()->json(GuideCategory::create($data), 201);
    }

    public function updateIcon(Request $request, GuideCategory $guideCategory)
    {
        $request->validate(['icon' => ['required', 'image', 'max:2048']]);

        if ($guideCategory->icon_path) {
            Storage::disk('public')->delete($guideCategory->icon_path);
        }

        $path = $request->file('icon')->store('icons/categories', 'public');
        $guideCategory->update(['icon_path' => $path]);

        return response()->json($guideCategory->fresh());
    }
}
