<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guide extends Model
{
    protected $fillable = [
        'title', 'description', 'content', 'author', 'id_category', 'header_image', 'published',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GuideCategory::class, 'id_category');
    }
}
