<?php

namespace App\Models;

use Database\Factories\CircleMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleMessage extends Model
{
    /** @use HasFactory<CircleMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'circle_id',
        'user_id',
        'body',
    ];

    public function circle(): BelongsTo
    {
        return $this->belongsTo(ReadingCircle::class, 'circle_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
