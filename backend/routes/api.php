<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DietController;
use App\Http\Controllers\Api\ExerciseCategoryController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ExerciseProgressController;
use App\Http\Controllers\Api\GuideCategoryController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\PlateController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\RoutineCategoryController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);

Route::get('/routines/published', [RoutineController::class, 'published']);
Route::get('/routines/categories', [RoutineCategoryController::class, 'index']);
Route::get('/routines/categories/in-use', [RoutineCategoryController::class, 'inUse']);

Route::get('/guides/published', [GuideController::class, 'published']);
Route::get('/guides/categories', [GuideCategoryController::class, 'index']);
Route::get('/guides/categories/in-use', [GuideCategoryController::class, 'inUse']);

// Estas dos quedan últimas dentro de su recurso: cualquier ruta literal bajo
// /routines o /guides debe declararse ANTES para no chocar con el wildcard {id}.
Route::get('/routines/{routine}', [RoutineController::class, 'show']);
Route::get('/guides/{guide}', [GuideController::class, 'show']);

// Autenticadas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateMe']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/coaches', [UserController::class, 'coaches']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/profile-image', [UserController::class, 'uploadProfileImage']);
    Route::get('/users/{user}/routines', [RoutineController::class, 'byUser']);
    Route::post('/users/{user}/assign-routine', [RoutineController::class, 'assign']);
    Route::delete('/users/{user}/assign-routine', [RoutineController::class, 'unassign']);
    Route::get('/users/{user}/assigned-routine', [RoutineController::class, 'assigned']);
    Route::get('/users/{user}/coach-assigned-routine', [RoutineController::class, 'coachAssigned']);
    Route::get('/users/{user}/coach-assigned-diet', [DietController::class, 'coachAssigned']);

    Route::get('/routines', [RoutineController::class, 'index']);
    Route::post('/routines', [RoutineController::class, 'store']);
    Route::patch('/routines/{routine}', [RoutineController::class, 'update']);
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy']);
    Route::post('/routines/categories', [RoutineCategoryController::class, 'store']);

    Route::get('/exercises/categories', [ExerciseCategoryController::class, 'index']);
    Route::get('/exercises', [ExerciseController::class, 'index']);
    Route::get('/exercises/{exercise}', [ExerciseController::class, 'show']);
    Route::post('/exercises', [ExerciseController::class, 'store']);
    Route::patch('/exercises/{exercise}', [ExerciseController::class, 'update']);
    Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy']);

    Route::get('/exercises-progress/last', [ExerciseProgressController::class, 'last']);
    Route::get('/exercises-progress/history', [ExerciseProgressController::class, 'history']);
    Route::post('/exercises-progress', [ExerciseProgressController::class, 'store']);

    Route::get('/diets', [DietController::class, 'index']);
    Route::get('/diets/{diet}/full', [DietController::class, 'full']);
    Route::post('/diets', [DietController::class, 'store']);
    Route::patch('/diets/{diet}', [DietController::class, 'update']);
    Route::delete('/diets/{diet}', [DietController::class, 'destroy']);

    Route::get('/plates', [PlateController::class, 'index']);
    Route::post('/plates', [PlateController::class, 'store']);
    Route::patch('/plates/{plate}', [PlateController::class, 'update']);
    Route::delete('/plates/{plate}', [PlateController::class, 'destroy']);

    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);
    Route::get('/ingredients/{ingredient}/used', [IngredientController::class, 'used']);
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::patch('/ingredients/{ingredient}', [IngredientController::class, 'update']);
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);

    Route::get('/guides', [GuideController::class, 'index']);
    Route::post('/guides', [GuideController::class, 'store']);
    Route::patch('/guides/{guide}', [GuideController::class, 'update']);
    Route::delete('/guides/{guide}', [GuideController::class, 'destroy']);
    Route::post('/guides/categories', [GuideCategoryController::class, 'store']);
    Route::post('/guides/categories/{guideCategory}/icon', [GuideCategoryController::class, 'updateIcon']);

    Route::get('/reviews', [ProgressController::class, 'index']);
    Route::get('/reviews/{progress}', [ProgressController::class, 'show']);
    Route::post('/reviews', [ProgressController::class, 'store']);
    Route::patch('/reviews/{progress}', [ProgressController::class, 'update']);
    Route::delete('/reviews/{progress}', [ProgressController::class, 'destroy']);

    Route::post('/stripe/confirm', [StripeController::class, 'confirm']);

    Route::post('/uploads/exercises', [UploadController::class, 'exercises']);
    Route::post('/uploads/guides', [UploadController::class, 'guides']);
    Route::delete('/uploads', [UploadController::class, 'destroy']);
});
