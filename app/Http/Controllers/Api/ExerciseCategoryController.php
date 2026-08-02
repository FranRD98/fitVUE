<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseCategory;

class ExerciseCategoryController extends Controller
{
    public function index()
    {
        return response()->json(ExerciseCategory::orderBy('category_name')->get());
    }
}
