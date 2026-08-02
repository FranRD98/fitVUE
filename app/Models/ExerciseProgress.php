<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseProgress extends Model
{
    const UPDATED_AT = null;

    protected $table = 'exercises_progress';

    protected $fillable = ['user_id', 'id_routine', 'exercise_id', 'exercise_name', 'day', 'sets', 'created_at'];

    protected function casts(): array
    {
        return [
            'sets' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class, 'id_routine');
    }
}
