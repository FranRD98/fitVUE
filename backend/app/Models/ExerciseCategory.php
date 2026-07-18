<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseCategory extends Model
{
    protected $table = 'exercises_categories';

    protected $fillable = ['category_name'];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'id_category');
    }
}
