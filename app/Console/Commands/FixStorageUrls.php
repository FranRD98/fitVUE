<?php

namespace App\Console\Commands;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fix-storage-urls')]
#[Description('Reescribe las URLs de imagen (perfil de usuario, cabecera de guía) que quedaron apuntando a un APP_URL antiguo/local, usando el APP_URL actual')]
class FixStorageUrls extends Command
{
    public function handle(): int
    {
        $correctBase = rtrim(config('app.url'), '/');
        $fixed = 0;

        foreach ([User::class => 'profile_image', Guide::class => 'header_image'] as $model => $column) {
            $rows = $model::query()
                ->whereNotNull($column)
                ->where($column, 'like', 'http%/storage/%')
                ->where($column, 'not like', "{$correctBase}/%")
                ->get();

            foreach ($rows as $row) {
                $path = preg_replace('#^https?://[^/]+/storage/#', '', $row->{$column});
                $newUrl = "{$correctBase}/storage/{$path}";

                $this->line("  {$model}#{$row->id}: {$row->{$column}} -> {$newUrl}");
                $row->update([$column => $newUrl]);
                $fixed++;
            }
        }

        $this->info($fixed
            ? "Corregidas {$fixed} URLs para usar {$correctBase}."
            : 'No se encontraron URLs desactualizadas.');

        return self::SUCCESS;
    }
}
