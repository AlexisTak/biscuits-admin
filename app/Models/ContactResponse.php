<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'respondable_id',
        'respondable_type',
        'user_id',
        'message',
        'is_internal_note',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relations
    public function respondable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }
}