<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuideCategory extends Model
{
    protected $table = 'guides_categories';

    protected $fillable = ['title', 'icon_path'];

    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class, 'id_category');
    }
}
