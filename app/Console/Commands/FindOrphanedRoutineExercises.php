<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('routines:find-orphaned-exercises')]
#[Description('Lista las rutinas que referencian ejercicios ya borrados del catálogo (routines.exercises es JSON sin relación real)')]
class FindOrphanedRoutineExercises extends Command
{
    public function handle(): int
    {
        $existingIds = Exercise::pluck('id')->all();
        $found = 0;

        Routine::whereNotNull('exercises')->chunk(50, function ($routines) use ($existingIds, &$found) {
            foreach ($routines as $routine) {
                $orphaned = collect($routine->exercises ?? [])
                    ->filter(fn ($ex) => ! in_array($ex['id'] ?? null, $existingIds, true));

                if ($orphaned->isEmpty()) {
                    continue;
                }

                $found++;
                $this->warn("Rutina #{$routine->id} \"{$routine->title}\" (user_id {$routine->user_id}):");
                foreach ($orphaned as $ex) {
                    $this->line('  - ejercicio borrado id '.($ex['id'] ?? '?').' "'.($ex['name'] ?? '?').'"');
                }
            }
        });

        if ($found === 0) {
            $this->info('No se encontraron referencias a ejercicios borrados.');
        }

        return self::SUCCESS;
    }
}
