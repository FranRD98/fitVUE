<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Routine extends Model
{
    protected $fillable = [
        'title', 'description', 'id_category', 'days', 'published', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoutineCategory::class, 'id_category');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
