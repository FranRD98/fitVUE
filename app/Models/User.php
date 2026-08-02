<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'last_name', 'email', 'password', 'role', 'plan_id',
    'coach_uid', 'assigned_routine', 'assigned_routine_by_coach', 'assigned_diet',
    'profile_image', 'completed_form',
    'birthday', 'gender', 'goal', 'height', 'weight', 'age', 'activity',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['uid'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'completed_form' => 'boolean',
            'plan_id' => 'integer',
            'birthday' => 'date',
            'height' => 'float',
            'weight' => 'float',
            'age' => 'integer',
        ];
    }

    public function getUidAttribute(): int
    {
        return $this->id;
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_uid');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'coach_uid');
    }

    public function assignedRoutine(): BelongsTo
    {
        return $this->belongsTo(Routine::class, 'assigned_routine');
    }

    public function assignedRoutineByCoach(): BelongsTo
    {
        return $this->belongsTo(Routine::class, 'assigned_routine_by_coach');
    }

    public function assignedDiet(): BelongsTo
    {
        return $this->belongsTo(Diet::class, 'assigned_diet');
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'user_id');
    }

    public function diets(): HasMany
    {
        return $this->hasMany(Diet::class, 'user_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'created_by');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'created_by');
    }

    public function plates(): HasMany
    {
        return $this->hasMany(Plate::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Progress::class, 'user_id');
    }
}
