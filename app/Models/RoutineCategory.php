<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineCategory extends Model
{
    protected $table = 'routines_categories';

    protected $fillable = ['title', 'icon_path'];

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'id_category');
    }
}
