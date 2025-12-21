<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Devis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'devis';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'service',
        'budget',
        'message',
        'status',
        'notes',
        'priority',
        'amount',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'priority' => 'normal',
    ];

    /**
     * ✅ Relation avec le contact (via email)
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'email', 'email');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor : Nom du statut traduit
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'processed' => 'Traité',
            default => ucfirst($this->status),
        };
    }

    /**
     * Accessor : Badge de priorité
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'danger',
            'normal' => 'warning',
            'low' => 'success',
            default => 'secondary',
        };
    }
}