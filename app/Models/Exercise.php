<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = ['name', 'description', 'id_category', 'equipment', 'image', 'created_by'];

    // Las rutinas guardan sus ejercicios como JSON suelto (Routine::$exercises),
    // sin relación real en BD: si no se mantienen sincronizados aquí, un
    // ejercicio borrado deja referencias huérfanas y uno renombrado deja el
    // nombre desactualizado en cada rutina que lo use.
    protected static function booted(): void
    {
        static::updated(function (Exercise $exercise) {
            if ($exercise->wasChanged('name')) {
                self::eachRoutineWithExercise($exercise->id, function (Routine $routine) use ($exercise) {
                    return collect($routine->exercises)->map(function ($item) use ($exercise) {
                        if (($item['id'] ?? null) === $exercise->id) {
                            $item['name'] = $exercise->name;
                        }

                        return $item;
                    })->all();
                });
            }
        });

        static::deleting(function (Exercise $exercise) {
            self::eachRoutineWithExercise($exercise->id, function (Routine $routine) use ($exercise) {
                return collect($routine->exercises)
                    ->reject(fn ($item) => ($item['id'] ?? null) === $exercise->id)
                    ->values()
                    ->all();
            });
        });
    }

    // Recorre las rutinas que referencian a este ejercicio y guarda el resultado
    // de $transform solo si de verdad cambia algo, para no reescribir de más.
    private static function eachRoutineWithExercise(int $exerciseId, callable $transform): void
    {
        Routine::whereNotNull('exercises')->chunkById(100, function ($routines) use ($exerciseId, $transform) {
            foreach ($routines as $routine) {
                $hasIt = collect($routine->exercises)->contains(fn ($item) => ($item['id'] ?? null) === $exerciseId);

                if (! $hasIt) {
                    continue;
                }

                $updated = $transform($routine);

                if ($updated !== $routine->exercises) {
                    $routine->exercises = $updated;
                    $routine->save();
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class, 'id_category');
    }

    public function secondaryMuscles(): BelongsToMany
    {
        return $this->belongsToMany(
            ExerciseCategory::class,
            'exercise_secondary_muscles',
            'exercise_id',
            'exercise_category_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ExerciseProgress::class, 'exercise_id');
    }
}
