<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = ['name', 'description', 'id_category', 'equipment', 'image', 'created_by'];

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
