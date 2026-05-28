<?php

namespace App\Models;

use App\Enums\ReactionType;
use Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    /** @use HasFactory<ReactionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'user_id',
        'type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReactionType::class,
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
