<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diet extends Model
{
    protected $fillable = ['title', 'description', 'user_id', 'meals', 'is_catalog', 'published'];

    protected function casts(): array
    {
        return [
            'meals' => 'array',
            'is_catalog' => 'boolean',
            'published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
