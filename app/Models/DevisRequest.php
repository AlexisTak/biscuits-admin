<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DevisRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'service',
        'budget',
        'message',
        'ip_address',
        'user_agent',
        'fingerprint',
        'status',
        'is_read',
        'priority',
        'quoted_amount',
        'quote_details',
        'quoted_at',
        'read_at',
        'archived_at',
        'assigned_to',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'quoted_amount' => 'decimal:2',
        'quoted_at' => 'datetime',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'priority' => 'normal',
        'is_read' => false,
    ];

    // Relations
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function responses(): MorphMany
    {
        return $this->morphMany(ContactResponse::class, 'respondable');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helpers
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function sendQuote(float $amount, ?string $details = null): void
    {
        $this->update([
            'status' => 'quoted',
            'quoted_amount' => $amount,
            'quote_details' => $details,
            'quoted_at' => now(),
        ]);
    }

    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public function markAsSpam(): void
    {
        $this->update(['status' => 'spam']);
    }
}