<?php

namespace Database\Seeders;

use App\Models\Diet;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Routine;
use App\Models\RoutineCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'last_name' => 'FitVue', 'email' => 'admin@fitvue.test',
            'password' => 'password', 'role' => 'admin', 'plan_id' => 3,
        ]);

        $coach = User::create([
            'name' => 'Carlos', 'last_name' => 'Coach', 'email' => 'coach@fitvue.test',
            'password' => 'password', 'role' => 'coach', 'plan_id' => 3,
        ]);

        $user = User::create([
            'name' => 'Laura', 'last_name' => 'Usuaria', 'email' => 'user@fitvue.test',
            'password' => 'password', 'role' => 'user', 'plan_id' => 2, 'coach_uid' => $coach->id,
        ]);

        $fuerza = RoutineCategory::create(['title' => 'Fuerza']);
        $cardio = RoutineCategory::create(['title' => 'Cardio']);

        $pecho = ExerciseCategory::create(['category_name' => 'Pecho']);
        $pierna = ExerciseCategory::create(['category_name' => 'Pierna']);
        $espalda = ExerciseCategory::create(['category_name' => 'Espalda']);

        $pressBanca = Exercise::create([
            'name' => 'Press banca', 'description' => 'Press de banca con barra', 'id_category' => $pecho->id, 'created_by' => $admin->id,
        ]);
        $sentadilla = Exercise::create([
            'name' => 'Sentadilla', 'description' => 'Sentadilla libre con barra', 'id_category' => $pierna->id, 'created_by' => $admin->id,
        ]);
        Exercise::create([
            'name' => 'Dominadas', 'description' => 'Dominadas con peso corporal', 'id_category' => $espalda->id, 'created_by' => $admin->id,
        ]);

        $routine = Routine::create([
            'title' => 'Rutina full body', 'description' => 'Rutina de 3 días para todo el cuerpo',
            'id_category' => $fuerza->id, 'published' => true, 'user_id' => $coach->id,
            'days' => [
                ['day' => 'Lunes', 'exercises' => [
                    ['id' => $pressBanca->id, 'name' => $pressBanca->name, 'sets' => 4, 'reps' => 10],
                ]],
                ['day' => 'Miércoles', 'exercises' => [
                    ['id' => $sentadilla->id, 'name' => $sentadilla->name, 'sets' => 4, 'reps' => 8],
                ]],
            ],
        ]);

        $user->update(['assigned_routine_by_coach' => $routine->id]);

        $pollo = Ingredient::create([
            'name' => 'Pechuga de pollo', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fats' => 3.6, 'created_by' => $coach->id,
        ]);
        $arroz = Ingredient::create([
            'name' => 'Arroz blanco cocido', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fats' => 0.3, 'created_by' => $coach->id,
        ]);

        $plate = Plate::create([
            'name' => 'Pollo con arroz', 'created_by' => $coach->id,
            'items' => [
                ['ingredient_id' => $pollo->id, 'quantity' => 150],
                ['ingredient_id' => $arroz->id, 'quantity' => 200],
            ],
        ]);

        $diet = Diet::create([
            'title' => 'Dieta de mantenimiento', 'user_id' => $coach->id,
            'meals' => [
                ['name' => 'Comida', 'items' => [
                    ['plate_id' => $plate->id, 'items' => [
                        ['ingredient_id' => $pollo->id, 'quantity' => 150],
                        ['ingredient_id' => $arroz->id, 'quantity' => 200],
                    ]],
                ]],
            ],
        ]);

        $user->update(['assigned_diet' => $diet->id]);

        $guideCategory = GuideCategory::create(['title' => 'Nutrición']);

        Guide::create([
            'title' => 'Cómo calcular tus macros', 'description' => 'Guía básica de macronutrientes',
            'content' => "Los macronutrientes son proteínas, carbohidratos y grasas...\n\nAjusta tu ingesta según tu objetivo.",
            'author' => 'Carlos Coach', 'id_category' => $guideCategory->id, 'published' => true,
        ]);
    }
}
