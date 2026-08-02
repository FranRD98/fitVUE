<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    const UPDATED_AT = null;

    protected $table = 'progress';

    protected $fillable = [
        'user_id', 'weight', 'neck', 'shoulders', 'chest', 'biceps_relaxed', 'biceps_flexed',
        'forearm', 'wrist', 'waist', 'abdomen', 'hips', 'quadriceps', 'calves', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
