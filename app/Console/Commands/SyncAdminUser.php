<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:sync {--password= : Sobrescribe la contraseña definida en ADMIN_PASSWORD}')]
#[Description('Crea o fuerza la cuenta admin garantizada (config/admin.php) con rol admin y contraseña conocida')]
class SyncAdminUser extends Command
{
    public function handle(): int
    {
        $email = config('admin.email');
        $password = $this->option('password') ?? config('admin.password');

        $user = User::firstOrNew(['email' => $email]);
        $wasNew = ! $user->exists;

        $user->fill([
            'name' => $user->name ?: config('admin.name'),
            'last_name' => $user->last_name ?: config('admin.last_name'),
            'role' => 'admin',
            'plan_id' => 3,
        ]);

        $user->password = $password; // El cast 'hashed' del modelo genera un hash bcrypt nuevo siempre.
        $user->save();

        $this->info(($wasNew ? 'Creada' : 'Actualizada')." la cuenta admin: {$email}");

        return self::SUCCESS;
    }
}
