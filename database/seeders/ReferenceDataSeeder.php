<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Catálogo de arranque (categorías, ejercicios, rutinas, dietas, ingredientes,
 * guías) para que la app no empiece vacía. Generado una vez a partir de datos
 * reales de producción, pero sin usuarios reales ni datos personales: todo
 * queda atribuido a la cuenta admin garantizada (ver `php artisan admin:sync`).
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('exercises_categories')->exists()) {
            $this->command?->warn('Ya hay datos de catálogo; se omite ReferenceDataSeeder.');

            return;
        }

        $admin = User::where('email', config('admin.email'))->first();

        if (! $admin) {
            $this->command?->warn('No existe la cuenta admin; ejecuta antes `php artisan admin:sync`.');

            return;
        }

        DB::table('routines_categories')->insert([
            [
                'id' => 1,
                'title' => 'Entrenamientos',
                'icon_path' => 'icons/routines/entrenamiento.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Mindfulness',
                'icon_path' => 'icons/routines/mindfulness.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'Estiramientos',
                'icon_path' => 'icons/routines/estiramientos.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => 'Yoga',
                'icon_path' => 'icons/routines/yoga.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('routines_categories');

        DB::table('exercises_categories')->insert([
            [
                'id' => 2,
                'category_name' => 'Glúteos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'category_name' => 'Pecho',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'category_name' => 'Abdomen',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'category_name' => 'Espalda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'category_name' => 'Hombros',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'category_name' => 'Femoral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'category_name' => 'Cuádriceps ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'category_name' => 'Gemelos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'category_name' => 'Biceps',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'category_name' => 'Triceps',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('exercises_categories');

        DB::table('guides_categories')->insert([
            [
                'id' => 3,
                'title' => 'Recetas',
                'icon_path' => 'icons/guides/recipes.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => 'Suplementación',
                'icon_path' => 'icons/guides/supplementation.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 1,
                'title' => 'Dietas',
                'icon_path' => 'icons/guides/diets.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Hábitos',
                'icon_path' => 'icons/guides/habits.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('guides_categories');

        DB::table('exercises')->insert([
            [
                'id' => 1,
                'name' => 'Peso muerto rumano',
                'description' => 'Ejercicio bueno para cadera posterior',
                'id_category' => 7,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Femoral sentado',
                'description' => 'Trabajar el femoral sentado',
                'id_category' => 7,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Hiperextensiones',
                'description' => 'Glúteos',
                'id_category' => 2,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Gemelo de pie',
                'description' => 'Ejercicio para gemelos',
                'id_category' => 9,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Press banca',
                'description' => 'Pechito fuerte',
                'id_category' => 3,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Press militar',
                'description' => 'Ejercicio press militar',
                'id_category' => 6,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'L-Sit',
                'description' => 'Ejercicio buenisimo para el core',
                'id_category' => 4,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 23,
                'name' => 'Fondos',
                'description' => NULL,
                'id_category' => 11,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'name' => 'Press banca plano con barra',
                'description' => NULL,
                'id_category' => 3,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'name' => 'Press inclinado con mancuernas',
                'description' => NULL,
                'id_category' => 3,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 21,
                'name' => 'Elevaciones laterales',
                'description' => NULL,
                'id_category' => 6,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 22,
                'name' => 'Press militar con barra',
                'description' => NULL,
                'id_category' => 6,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 24,
                'name' => 'Extensión de tríceps en polea',
                'description' => NULL,
                'id_category' => 11,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 25,
                'name' => 'Dominadas ',
                'description' => NULL,
                'id_category' => 5,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 26,
                'name' => 'Remo con barra',
                'description' => NULL,
                'id_category' => 5,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 27,
                'name' => 'Face pulls (polea alta)',
                'description' => NULL,
                'id_category' => 5,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 28,
                'name' => 'Curl bíceps',
                'description' => NULL,
                'id_category' => 10,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 29,
                'name' => 'Curl martillo',
                'description' => NULL,
                'id_category' => 10,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 30,
                'name' => 'Elevaciones de piernas',
                'description' => NULL,
                'id_category' => 4,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 31,
                'name' => 'Sentadillas',
                'description' => NULL,
                'id_category' => 8,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 32,
                'name' => 'Prensa de piernas',
                'description' => NULL,
                'id_category' => 8,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 33,
                'name' => 'Peso muerto rumano',
                'description' => NULL,
                'id_category' => 7,
                'image' => NULL,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('exercises');

        DB::table('routines')->insert([
            [
                'id' => 14,
                'title' => 'Rutina para Tiana 27-05-2025',
                'description' => 'Rutina mujer joven Hipertrofia',
                'id_category' => 1,
                'days' => '[{"day": "Lunes", "exercises": [{"id": 25, "name": "Dominadas ", "reps": 12, "sets": 2}, {"id": 27, "name": "Face pulls (polea alta)", "reps": 12, "sets": 2}, {"id": 23, "name": "Fondos", "reps": 10, "sets": 3}]}, {"day": "Miércoles", "exercises": [{"id": 7, "name": "Press militar", "reps": 10, "sets": 2}, {"id": 23, "name": "Fondos", "reps": 10, "sets": 2}]}, {"day": "Viernes", "exercises": [{"id": 27, "name": "Face pulls (polea alta)", "reps": 12, "sets": 2}, {"id": 4, "name": "Gemelo de pie", "reps": 20, "sets": 3}]}]',
                'published' => true,
                'user_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'title' => 'Rutina 3 días (Full-body dividida)',
                'description' => 'Objetivo: Hipertrofia + Fuerza básica Nivel: Principiante a intermedio Duración por sesión: 60-75 min Frecuencia: Lunes – Miércoles – Viernes (flexible)',
                'id_category' => 1,
                'days' => '[{"day": "Lunes", "exercises": [{"id": 19, "name": "Press banca plano con barra", "reps": 9, "sets": 4}, {"id": 20, "name": "Press inclinado con mancuernas", "reps": 10, "sets": 3}, {"id": 21, "name": "Elevaciones laterales", "reps": 12, "sets": 3}, {"id": 22, "name": "Press militar con barra", "reps": 8, "sets": 3}, {"id": 23, "name": "Fondos", "reps": 10, "sets": 3}, {"id": 24, "name": "Extensión de tríceps en polea", "reps": 12, "sets": 3}]}, {"day": "Miércoles", "exercises": [{"id": 25, "name": "Dominadas ", "reps": 8, "sets": 4}, {"id": 26, "name": "Remo con barra", "reps": 10, "sets": 4}, {"id": 27, "name": "Face pulls (polea alta)", "reps": 15, "sets": 3}, {"id": 28, "name": "Curl bíceps", "reps": 10, "sets": 3}, {"id": 29, "name": "Curl martillo", "reps": 12, "sets": 3}]}, {"day": "Viernes", "exercises": [{"id": 31, "name": "Sentadillas", "reps": 8, "sets": 4}, {"id": 32, "name": "Prensa de piernas", "reps": 12, "sets": 3}, {"id": 33, "name": "Peso muerto rumano", "reps": 10, "sets": 3}]}]',
                'published' => true,
                'user_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('routines');

        DB::table('ingredients')->insert([
            [
                'id' => 12,
                'name' => 'Pan integral',
                'calories' => 250.0,
                'protein' => 9.0,
                'carbs' => 42.0,
                'fats' => 4.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'name' => 'Aguacate',
                'calories' => 160.0,
                'protein' => 2.0,
                'carbs' => 9.0,
                'fats' => 15.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'name' => 'Queso feta',
                'calories' => 264.0,
                'protein' => 14.0,
                'carbs' => 4.0,
                'fats' => 21.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'name' => 'Tomates Cherry',
                'calories' => 18.0,
                'protein' => 1.0,
                'carbs' => 4.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'name' => 'Semillas de chía',
                'calories' => 486.0,
                'protein' => 17.0,
                'carbs' => 42.0,
                'fats' => 31.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 18,
                'name' => 'Quinoa cocida',
                'calories' => 120.0,
                'protein' => 4.0,
                'carbs' => 21.0,
                'fats' => 2.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'name' => 'Aceitunas negras',
                'calories' => 115.0,
                'protein' => 1.0,
                'carbs' => 6.0,
                'fats' => 11.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'name' => 'Espinacas frescas',
                'calories' => 23.0,
                'protein' => 3.0,
                'carbs' => 4.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 22,
                'name' => 'Zumo de limón',
                'calories' => 22.0,
                'protein' => 0.0,
                'carbs' => 7.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 23,
                'name' => 'Lentejas',
                'calories' => 116.0,
                'protein' => 9.0,
                'carbs' => 20.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 24,
                'name' => 'Zanahoria cruda',
                'calories' => 41.0,
                'protein' => 1.0,
                'carbs' => 10.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 25,
                'name' => 'Apio',
                'calories' => 16.0,
                'protein' => 0.0,
                'carbs' => 3.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 26,
                'name' => 'Tomate triturado',
                'calories' => 32.0,
                'protein' => 2.0,
                'carbs' => 7.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 27,
                'name' => 'Ajo',
                'calories' => 149.0,
                'protein' => 6.0,
                'carbs' => 33.0,
                'fats' => 0.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 28,
                'name' => 'Aceite de oliva',
                'calories' => 884.0,
                'protein' => 0.0,
                'carbs' => 0.0,
                'fats' => 100.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 17,
                'name' => 'Pechuga de pollo',
                'calories' => 165.0,
                'protein' => 31.0,
                'carbs' => 0.0,
                'fats' => 4.0,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('ingredients');

        DB::table('plates')->insert([
            [
                'id' => 12,
                'name' => 'Tostada del Olimpo',
                'items' => '[{"quantity": 60, "ingredient_id": 12}, {"quantity": 50, "ingredient_id": 13}, {"quantity": 30, "ingredient_id": 14}, {"quantity": 40, "ingredient_id": 15}, {"quantity": 10, "ingredient_id": 16}]',
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'name' => 'Pollo Ateniense con Quinoa',
                'items' => '[{"quantity": 150, "ingredient_id": 17}, {"quantity": 100, "ingredient_id": 18}, {"quantity": 30, "ingredient_id": 19}, {"quantity": 40, "ingredient_id": 20}, {"quantity": 10, "ingredient_id": 28}, {"quantity": 10, "ingredient_id": 22}]',
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'name' => 'Sopa de lentejas',
                'items' => '[{"quantity": 150, "ingredient_id": 23}, {"quantity": 50, "ingredient_id": 24}, {"quantity": 40, "ingredient_id": 25}, {"quantity": 60, "ingredient_id": 26}, {"quantity": 5, "ingredient_id": 27}, {"quantity": 5, "ingredient_id": 28}]',
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('plates');

        DB::table('diets')->insert([
            [
                'id' => 16,
                'title' => 'Prueba',
                'description' => '1234',
                'user_id' => $admin->id,
                'meals' => '[{"name": "Desayuno", "items": [{"id": 13, "name": "Pollo Ateniense con Quinoa", "items": [{"quantity": 150, "ingredient": {"id": 17, "fats": 4, "name": "Pechuga de pollo", "carbs": 0, "protein": 31, "calories": 165, "created_at": "2025-05-27T10:29:57.530934+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 17}, {"quantity": 100, "ingredient": {"id": 18, "fats": 2, "name": "Quinoa cocida", "carbs": 21, "protein": 4, "calories": 120, "created_at": "2025-05-27T10:30:14.097249+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 18}, {"quantity": 30, "ingredient": {"id": 19, "fats": 11, "name": "Aceitunas negras", "carbs": 6, "protein": 1, "calories": 115, "created_at": "2025-05-27T10:30:21.975227+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 19}, {"quantity": 40, "ingredient": {"id": 20, "fats": 0, "name": "Espinacas frescas", "carbs": 4, "protein": 3, "calories": 23, "created_at": "2025-05-27T10:30:32.910904+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 20}, {"quantity": 10, "ingredient": {"id": 28, "fats": 100, "name": "Aceite de oliva", "carbs": 0, "protein": 0, "calories": 884, "created_at": "2025-05-27T10:31:46.630137+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 28}, {"quantity": 10, "ingredient": {"id": 22, "fats": 0, "name": "Zumo de limón", "carbs": 7, "protein": 0, "calories": 22, "created_at": "2025-05-27T10:30:59.315386+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 22}], "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}], "enabled": true}]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'title' => 'Dieta para Tiana',
                'description' => 'Recomposición corporal',
                'user_id' => $admin->id,
                'meals' => '[{"name": "Desayuno", "items": [{"id": 12, "name": "Tostada del Olimpo", "items": [{"quantity": 60, "ingredient": {"id": 12, "fats": 4, "name": "Pan integral", "carbs": 42, "protein": 9, "calories": 250, "created_at": "2025-05-27T10:28:45.61644+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 12}, {"quantity": 50, "ingredient": {"id": 13, "fats": 15, "name": "Aguacate", "carbs": 9, "protein": 2, "calories": 160, "created_at": "2025-05-27T10:28:57.837207+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 13}, {"quantity": 30, "ingredient": {"id": 14, "fats": 21, "name": "Queso feta", "carbs": 4, "protein": 14, "calories": 264, "created_at": "2025-05-27T10:29:07.67913+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 14}, {"quantity": 40, "ingredient": {"id": 15, "fats": 0, "name": "Tomates Cherry", "carbs": 4, "protein": 1, "calories": 18, "created_at": "2025-05-27T10:29:29.062114+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 15}, {"quantity": 10, "ingredient": {"id": 16, "fats": 31, "name": "Semillas de chía", "carbs": 42, "protein": 17, "calories": 486, "created_at": "2025-05-27T10:29:41.093394+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 16}], "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}], "enabled": true}, {"name": "Comida", "items": [{"id": 13, "name": "Pollo Ateniense con Quinoa", "items": [{"quantity": 150, "ingredient": {"id": 17, "fats": 4, "name": "Pechuga de pollo", "carbs": 0, "protein": 31, "calories": 165, "created_at": "2025-05-27T10:29:57.530934+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 17}, {"quantity": 100, "ingredient": {"id": 18, "fats": 2, "name": "Quinoa cocida", "carbs": 21, "protein": 4, "calories": 120, "created_at": "2025-05-27T10:30:14.097249+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 18}, {"quantity": 30, "ingredient": {"id": 19, "fats": 11, "name": "Aceitunas negras", "carbs": 6, "protein": 1, "calories": 115, "created_at": "2025-05-27T10:30:21.975227+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 19}, {"quantity": 40, "ingredient": {"id": 20, "fats": 0, "name": "Espinacas frescas", "carbs": 4, "protein": 3, "calories": 23, "created_at": "2025-05-27T10:30:32.910904+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 20}, {"quantity": 10, "ingredient": {"id": 28, "fats": 100, "name": "Aceite de oliva", "carbs": 0, "protein": 0, "calories": 884, "created_at": "2025-05-27T10:31:46.630137+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 28}, {"quantity": 10, "ingredient": {"id": 22, "fats": 0, "name": "Zumo de limón", "carbs": 7, "protein": 0, "calories": 22, "created_at": "2025-05-27T10:30:59.315386+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 22}], "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}], "enabled": true}, {"name": "Cena", "items": [{"id": 14, "name": "Sopa de lentejas", "items": [{"quantity": 150, "ingredient": {"id": 23, "fats": 0, "name": "Lentejas", "carbs": 20, "protein": 9, "calories": 116, "created_at": "2025-05-27T10:31:08.820058+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 23}, {"quantity": 50, "ingredient": {"id": 24, "fats": 0, "name": "Zanahoria cruda", "carbs": 10, "protein": 1, "calories": 41, "created_at": "2025-05-27T10:31:17.177751+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 24}, {"quantity": 40, "ingredient": {"id": 25, "fats": 0, "name": "Apio", "carbs": 3, "protein": 0, "calories": 16, "created_at": "2025-05-27T10:31:23.204007+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 25}, {"quantity": 60, "ingredient": {"id": 26, "fats": 0, "name": "Tomate triturado", "carbs": 7, "protein": 2, "calories": 32, "created_at": "2025-05-27T10:31:31.711597+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 26}, {"quantity": 5, "ingredient": {"id": 27, "fats": 0, "name": "Ajo", "carbs": 33, "protein": 6, "calories": 149, "created_at": "2025-05-27T10:31:39.541136+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 27}, {"quantity": 5, "ingredient": {"id": 28, "fats": 100, "name": "Aceite de oliva", "carbs": 0, "protein": 0, "calories": 884, "created_at": "2025-05-27T10:31:46.630137+00:00", "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}, "ingredient_id": 28}], "created_by": "488760e0-47f3-40f5-916c-6bd9759926fd"}], "enabled": true}]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('diets');

        DB::table('guides')->insert([
            [
                'id' => 5,
                'title' => 'Ejercicio Inteligente: Más Allá de los 10,000 Pasos',
                'description' => 'Explora cómo incorporar ejercicios efectivos en tu rutina diaria para mejorar tu salud física y mental.',
                'content' => 'Más allá de caminar:
Estudios recientes sugieren que 10 minutos de ejercicio vigoroso diario pueden ser más beneficiosos que caminar 10,000 pasos.

Beneficios del ejercicio regular:

Mejora de la salud cardiovascular y pulmonar.

Reducción del estrés, la ansiedad y la depresión.

Fortalecimiento muscular y óseo.

Consejos prácticos:

Incorpora ejercicios de fuerza y flexibilidad.

Encuentra actividades que disfrutes para mantener la constancia.

Escucha a tu cuerpo y ajusta la intensidad según tus necesidades.',
                'author' => 'Fran Riera',
                'id_category' => 2,
                'header_image' => Storage::disk('public')->exists('guides/1747727051862-caminar.jpg') ? Storage::disk('public')->url('guides/1747727051862-caminar.jpg') : null,
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'title' => 'Suplementos: ¿Necesarios o Exagerados?',
                'description' => 'Aprende a identificar cuándo los suplementos son realmente necesarios y cómo elegirlos adecuadamente.',
                'content' => '¿Cuándo considerar suplementos?

Deficiencias nutricionales diagnosticadas.

Necesidades específicas como embarazo o envejecimiento.

Dietas restrictivas que limitan ciertos nutrientes.

Elección inteligente:

Consulta con un profesional de la salud antes de comenzar cualquier suplemento.

Verifica la calidad y certificaciones del producto.

Evita megadosis innecesarias que pueden ser perjudiciales.',
                'author' => 'Fran RIera',
                'id_category' => 1,
                'header_image' => Storage::disk('public')->exists('guides/1747727044149-Suplementos.jpg') ? Storage::disk('public')->url('guides/1747727044149-Suplementos.jpg') : null,
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'Omega-3: Tu Aliado Silencioso para el Corazón y el Cerebro',
                'description' => 'Descubre por qué los ácidos grasos omega-3 son esenciales para tu salud cardiovascular y cerebral, y cómo incorporarlos eficazmente en tu dieta.',
                'content' => '¿Qué son los omega-3?
Son ácidos grasos esenciales que el cuerpo no produce por sí mismo. Se encuentran en pescados grasos como el salmón, las sardinas y la caballa, así como en nueces y semillas de lino.

Beneficios clave:

Reducción de triglicéridos y presión arterial.

Propiedades antiinflamatorias y antitrombóticas.

Mejora de la salud cerebral y visual.

¿Necesito suplementos?
Si tu consumo de pescado es bajo, considera suplementos de aceite de pescado. Es importante elegir productos certificados por su pureza y contenido de EPA y DHA.',
                'author' => 'Fran Riera',
                'id_category' => 4,
                'header_image' => Storage::disk('public')->exists('guides/1747727068943-Omega3.jpg') ? Storage::disk('public')->url('guides/1747727068943-Omega3.jpg') : null,
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => 'Dieta Mediterránea: El Secreto de la Longevidad',
                'description' => 'Adopta el estilo de vida mediterráneo y descubre cómo esta dieta puede mejorar tu salud y bienestar general.',
                'content' => 'Principios básicos:

Consumo elevado de frutas, verduras, legumbres y cereales integrales.

Uso de aceite de oliva como principal fuente de grasa.

Ingesta moderada de pescado y productos lácteos.

Bajo consumo de carnes rojas y procesadas.

Beneficios:

Reducción del riesgo de enfermedades cardiovasculares.

Mejora de la salud metabólica y cognitiva.

Promoción de una vida más larga y saludable.',
                'author' => 'Fran RIera',
                'id_category' => 1,
                'header_image' => Storage::disk('public')->exists('guides/1747727059401-mediterranea.jpg') ? Storage::disk('public')->url('guides/1747727059401-mediterranea.jpg') : null,
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'title' => 'Alimentación Saludable: Consejos Prácticos para el Día a Día',
                'description' => 'Implementa cambios sencillos pero efectivos en tu alimentación para mejorar tu salud y bienestar.',
                'content' => 'Consejos clave:

Consume una variedad de alimentos de todos los grupos.

Limita el consumo de azúcares añadidos, grasas saturadas y sodio.

Mantén una hidratación adecuada.

Planificación efectiva:

Prepara tus comidas con antelación.

Lee las etiquetas nutricionales para tomar decisiones informadas.

Escoge métodos de cocción saludables como al vapor o al horno.',
                'author' => 'Fran Riera',
                'id_category' => 1,
                'header_image' => Storage::disk('public')->exists('guides/1747727033023-Dieta.jpg') ? Storage::disk('public')->url('guides/1747727033023-Dieta.jpg') : null,
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->resetAutoIncrement('guides');

    }

    private function resetAutoIncrement(string $table): void
    {
        $max = DB::table($table)->max('id') ?? 0;
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = ".($max + 1));
    }
}
