<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Devis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'devis'; // Nom de la table au pluriel

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
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ✅ RELATION INVERSE : Un devis appartient à un contact (via email)
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'email', 'email');
    }

    /**
     * Accessor : Nom du statut traduit
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
            'processed' => 'Traité',
            default => ucfirst($this->status),
        };
    }
}