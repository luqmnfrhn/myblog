<?php

namespace App\Models;

use Database\Factories\ReadingCircleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingCircle extends Model
{
    /** @use HasFactory<ReadingCircleFactory> */
    use HasFactory;

    protected $fillable = [
        'post_id',
        'creator_id',
        'name',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'circle_members', 'circle_id', 'user_id')
            ->withPivot('joined_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CircleMessage::class, 'circle_id')->with('author')->latest();
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->getKey())->exists();
    }
}
