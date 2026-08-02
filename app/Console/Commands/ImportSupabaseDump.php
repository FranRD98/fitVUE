<?php

namespace App\Console\Commands;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

#[Signature('app:import-supabase-dump {path : Ruta al .sql/.backup descomprimido (pg_dumpall)}')]
#[Description('Importa los datos reales de un dump de Supabase (Postgres) a la base de datos MySQL local')]
class ImportSupabaseDump extends Command
{
    private array $userMap = []; // uuid (auth/public.uid) => nuevo id entero MySQL
    private array $pendingUserLinks = []; // nuevo id => [coach_uid, assigned_routine, assigned_diet, assigned_routine_by_coach]

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("No existe el archivo: {$path}");

            return self::FAILURE;
        }

        $content = file_get_contents($path);

        if (! $this->confirm('Esto vaciará las tablas de datos actuales de MySQL y las sustituirá por las del dump. ¿Continuar?', true)) {
            return self::SUCCESS;
        }

        // Nota: no se envuelve en una transacción porque resetAutoIncrement() ejecuta
        // ALTER TABLE, que en MySQL hace commit implícito y rompería la transacción.
        Schema::disableForeignKeyConstraints();

        foreach (['personal_access_tokens', 'exercises_progress', 'progress', 'diets', 'plates', 'ingredients',
            'exercises', 'routines', 'guides', 'routines_categories', 'exercises_categories',
            'guides_categories', 'users'] as $table) {
            DB::table($table)->delete();
        }

        $authUsers = $this->indexBy($this->parseCopyBlock($content, 'auth.users'), 'id');

        $this->importUsers($this->parseCopyBlock($content, 'public.users'), $authUsers);
        $this->importCategories($content, 'public.routines_categories', 'routines_categories');
        $this->importCategories($content, 'public.exercises_categories', 'exercises_categories');
        $this->importCategories($content, 'public.guides_categories', 'guides_categories');
        $this->importExercises($this->parseCopyBlock($content, 'public.exercises'));
        $this->importRoutines($this->parseCopyBlock($content, 'public.routines'));
        $this->importDiets($this->parseCopyBlock($content, 'public.diets'));
        $this->importIngredients($this->parseCopyBlock($content, 'public.ingredients'));
        $this->importPlates($this->parseCopyBlock($content, 'public.plates'));
        $this->importGuides($this->parseCopyBlock($content, 'public.guides'));
        $this->importProgress($this->parseCopyBlock($content, 'public.progress'));
        $this->importExercisesProgress($this->parseCopyBlock($content, 'public.exercises_progress'));

        $this->linkUsers();

        Schema::enableForeignKeyConstraints();

        // El dump trae la contraseña original de Supabase para admin@fitvue.es; forzamos
        // la cuenta admin garantizada para no perder el acceso conocido tras reimportar.
        $this->call('admin:sync');

        $this->relinkLocalImages();

        $this->info('Importación completada.');

        return self::SUCCESS;
    }

    /**
     * El dump apunta a URLs del bucket de Supabase; si los ficheros ya se copiaron a
     * storage/app/public (ver DEPLOY.md), reescribe las referencias a rutas locales.
     */
    private function relinkLocalImages(): void
    {
        $defaultProfile = 'icons/profile-images/default-profile.svg';

        if (! Storage::disk('public')->exists($defaultProfile)) {
            $this->warn('No se encontraron imágenes locales en storage/app/public; se mantienen las URLs de Supabase.');

            return;
        }

        $profileFiles = [
            'tianagt@outlook.com' => 'icons/profile-images/1ef1e25f-2c9d-4c77-91f3-09b156917042_1748364273622.jpg',
            'isabeldm@live.com' => 'icons/profile-images/08a8e217-61fa-4e9a-8344-8b5da414a435_1748350212757.jpg',
        ];

        foreach ($profileFiles as $email => $path) {
            if (Storage::disk('public')->exists($path)) {
                User::where('email', $email)->update(['profile_image' => Storage::disk('public')->url($path)]);
            }
        }

        User::whereNotIn('email', array_keys($profileFiles))
            ->update(['profile_image' => Storage::disk('public')->url($defaultProfile)]);

        $guideHeaders = [
            'Omega-3' => 'guides/1747727068943-Omega3.jpg',
            'Mediterr' => 'guides/1747727059401-mediterranea.jpg',
            'Inteligente' => 'guides/1747727051862-caminar.jpg',
            'Suplementos' => 'guides/1747727044149-Suplementos.jpg',
            'Saludable' => 'guides/1747727033023-Dieta.jpg',
        ];

        foreach ($guideHeaders as $needle => $path) {
            if (Storage::disk('public')->exists($path)) {
                Guide::where('title', 'like', "%{$needle}%")->update(['header_image' => Storage::disk('public')->url($path)]);
            }
        }

        foreach (GuideCategory::all() as $category) {
            if ($category->icon_path && str_starts_with($category->icon_path, '/')) {
                $category->update(['icon_path' => ltrim($category->icon_path, '/')]);
            }
        }

        $this->info('Referencias de imágenes reenlazadas al almacenamiento local.');
    }

    private function importUsers(array $rows, array $authUsers): void
    {
        foreach ($rows as $row) {
            $uid = $row['uid'];
            $auth = $authUsers[$uid] ?? null;

            $id = DB::table('users')->insertGetId([
                'name' => $this->nullify($row['name']) ?? '',
                'last_name' => $this->nullify($row['last_name']),
                'email' => $row['email'],
                'password' => $auth['encrypted_password'] ?? bcrypt(str()->random(32)),
                'role' => in_array($row['role'], ['user', 'coach', 'admin'], true) ? $row['role'] : 'user',
                'plan_id' => (int) $row['plan_id'],
                'profile_image' => $this->nullify($row['profile_image']),
                'completed_form' => $row['completedForm'] === 't',
                'birthday' => $this->parseDate($row['birthday']),
                'gender' => $this->nullify($row['gender']),
                'goal' => $this->nullify($row['goal']),
                'height' => $row['height'] !== null ? (float) $row['height'] : null,
                'weight' => $row['weight'] !== null ? (float) $row['weight'] : null,
                'age' => $row['age'] !== null ? (int) round((float) $row['age']) : null,
                'activity' => $this->nullify($row['activity']),
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['created_at']),
            ]);

            $this->userMap[$uid] = $id;

            $this->pendingUserLinks[$id] = [
                'coach_uid' => $row['coach_uid'],
                'assigned_routine' => $row['assigned_routine'],
                'assigned_diet' => $row['assigned_diet'],
                'assigned_routine_by_coach' => $row['assigned_routine_by_coach'],
            ];
        }

        $this->info(count($rows).' usuarios importados.');
    }

    private function linkUsers(): void
    {
        foreach ($this->pendingUserLinks as $id => $links) {
            DB::table('users')->where('id', $id)->update([
                'coach_uid' => $links['coach_uid'] ? ($this->userMap[$links['coach_uid']] ?? null) : null,
                'assigned_routine' => $links['assigned_routine'],
                'assigned_diet' => $links['assigned_diet'],
                'assigned_routine_by_coach' => $links['assigned_routine_by_coach'],
            ]);
        }
    }

    private function importCategories(string $content, string $sourceTable, string $destTable): void
    {
        $rows = $this->parseCopyBlock($content, $sourceTable);

        foreach ($rows as $row) {
            $data = [
                'id' => (int) $row['id'],
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['updated_at'] ?? $row['created_at']),
            ];

            if ($destTable === 'exercises_categories') {
                $data['category_name'] = $row['category_name'];
            } else {
                $data['title'] = $row['title'];
                $data['icon_path'] = $this->nullify($row['icon_path']);
            }

            DB::table($destTable)->insert($data);
        }

        $this->resetAutoIncrement($destTable);
        $this->info(count($rows)." filas importadas en {$destTable}.");
    }

    private function importExercises(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('exercises')->insert([
                'id' => (int) $row['id'],
                'name' => $this->nullify($row['name']) ?? '',
                'description' => $this->nullify($row['description']),
                'id_category' => $row['id_category'] !== null ? (int) $row['id_category'] : null,
                'image' => $row['image'] !== '' ? $row['image'] : null,
                'created_by' => $this->mapUser($row['created_by']),
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['updated_at'] ?? $row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('exercises');
        $this->info(count($rows).' ejercicios importados.');
    }

    private function importRoutines(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('routines')->insert([
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'description' => $this->nullify($row['description']),
                'id_category' => (int) $row['id_category'],
                'days' => $row['days'],
                'published' => $row['published'] === 't',
                'user_id' => $this->mapUser($row['user_id']),
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['updated_at'] ?? $row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('routines');
        $this->info(count($rows).' rutinas importadas.');
    }

    private function importDiets(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('diets')->insert([
                'id' => (int) $row['id'],
                'title' => $this->nullify($row['title']),
                'description' => $this->nullify($row['description']),
                'user_id' => $this->mapUser($row['user_id']),
                'meals' => $row['meals'],
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('diets');
        $this->info(count($rows).' dietas importadas.');
    }

    private function importIngredients(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('ingredients')->insert([
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'calories' => (float) $row['calories'],
                'protein' => (float) $row['protein'],
                'carbs' => (float) $row['carbs'],
                'fats' => (float) $row['fats'],
                'created_by' => $this->mapUser($row['created_by']),
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('ingredients');
        $this->info(count($rows).' ingredientes importados.');
    }

    private function importPlates(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('plates')->insert([
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'items' => $row['items'],
                'created_by' => $this->mapUser($row['created_by']),
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('plates');
        $this->info(count($rows).' platos importados.');
    }

    private function importGuides(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('guides')->insert([
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'description' => $this->nullify($row['description']),
                'content' => $this->nullify($row['content']),
                'author' => $this->nullify($row['author']),
                'id_category' => $row['id_category'] !== null ? (int) $row['id_category'] : null,
                'header_image' => $this->nullify($row['header_image']),
                'published' => $row['published'] === 't',
                'created_at' => $this->ts($row['created_at']),
                'updated_at' => $this->ts($row['updated_at'] ?? $row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('guides');
        $this->info(count($rows).' guías importadas.');
    }

    private function importProgress(array $rows): void
    {
        $fields = ['weight', 'neck', 'shoulders', 'chest', 'abdomen', 'biceps_relaxed', 'biceps_flexed',
            'forearm', 'wrist', 'hips', 'waist', 'quadriceps', 'calves'];

        foreach ($rows as $row) {
            $data = ['id' => (int) $row['id'], 'user_id' => $this->mapUser($row['user_id']), 'created_at' => $this->ts($row['created_at'])];

            foreach ($fields as $field) {
                $data[$field] = $row[$field] !== null ? (float) $row[$field] : null;
            }

            DB::table('progress')->insert($data);
        }

        $this->resetAutoIncrement('progress');
        $this->info(count($rows).' revisiones físicas importadas.');
    }

    private function importExercisesProgress(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('exercises_progress')->insert([
                'id' => (int) $row['id'],
                'user_id' => $this->mapUser($row['user_id']),
                'id_routine' => $row['id_routine'] !== null ? (int) $row['id_routine'] : null,
                'exercise_id' => (int) $row['exercise_id'],
                'exercise_name' => $this->nullify($row['exercise_name']),
                'day' => $this->nullify($row['day']),
                'sets' => $row['sets'],
                'created_at' => $this->ts($row['created_at']),
            ]);
        }

        $this->resetAutoIncrement('exercises_progress');
        $this->info(count($rows).' registros de progreso de ejercicios importados.');
    }

    private function ts(?string $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    private function mapUser(?string $uuid): ?int
    {
        if (! $uuid) {
            return null;
        }

        if (! isset($this->userMap[$uuid])) {
            $this->warn("UUID de usuario no encontrado, se deja NULL: {$uuid}");

            return null;
        }

        return $this->userMap[$uuid];
    }

    private function resetAutoIncrement(string $table): void
    {
        $max = DB::table($table)->max('id') ?? 0;
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = ".($max + 1));
    }

    private function nullify(?string $value): ?string
    {
        if ($value === null || strtolower(trim($value)) === 'null' || $value === '') {
            return null;
        }

        return $value;
    }

    private function parseDate(?string $value): ?string
    {
        $value = $this->nullify($value);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function indexBy(array $rows, string $key): array
    {
        $indexed = [];

        foreach ($rows as $row) {
            $indexed[$row[$key]] = $row;
        }

        return $indexed;
    }

    /**
     * Extrae y parsea un bloque "COPY tabla (...) FROM stdin; ... \." del dump de pg_dumpall.
     */
    private function parseCopyBlock(string $content, string $table): array
    {
        $lines = explode("\n", $content);
        $columns = null;
        $rows = [];
        $capturing = false;

        foreach ($lines as $line) {
            if (! $capturing) {
                if (preg_match('/^COPY '.preg_quote($table, '/').' \(([^)]*)\) FROM stdin;$/', $line, $m)) {
                    $columns = array_map(fn ($c) => trim(trim($c), '"'), explode(',', $m[1]));
                    $capturing = true;
                }

                continue;
            }

            if ($line === '\\.') {
                break;
            }

            $fields = explode("\t", $line);
            $row = [];

            foreach ($columns as $i => $col) {
                $raw = $fields[$i] ?? null;
                $row[$col] = $raw === '\N' ? null : $this->unescapeCopyValue($raw);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function unescapeCopyValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $result = '';
        $len = strlen($value);

        for ($i = 0; $i < $len; $i++) {
            $char = $value[$i];

            if ($char === '\\' && $i + 1 < $len) {
                $next = $value[$i + 1];
                $escaped = ['n' => "\n", 't' => "\t", 'r' => "\r", '\\' => '\\'][$next] ?? null;

                if ($escaped !== null) {
                    $result .= $escaped;
                    $i++;
                } else {
                    $result .= $char;
                }

                continue;
            }

            $result .= $char;
        }

        return $result;
    }
}
