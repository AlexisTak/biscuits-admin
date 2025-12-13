<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'service',
        'address',
        'zip_code',
        'message',
        'status',
        'notes',
        'priority',
        'is_read',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ✅ RELATION : Un contact peut avoir plusieurs devis
     * 
     * La relation se fait via l'email (un même email peut avoir plusieurs devis)
     */
    public function devis(): HasMany
    {
        return $this->hasMany(Devis::class, 'email', 'email');
    }

    /**
     * Scope : Contacts non lus
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope : Par statut
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope : Récents (moins de 7 jours)
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    /**
     * Accessor : Nom du statut traduit
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'processed' => 'Traité',
            'archived' => 'Archivé',
            default => ucfirst($this->status),
        };
    }

    /**
     * Accessor : Couleur du badge statut
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'processed' => 'green',
            'archived' => 'gray',
            default => 'blue',
        };
    }
}