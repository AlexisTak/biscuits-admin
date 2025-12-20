<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'fingerprint',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
        'fingerprint',
    ];

    // ============================================================================
    // RELATIONS
    // ============================================================================

    /**
     * COMMENTÉ : Relation avec les devis
     * Décommentez cette méthode une fois que vous aurez ajouté la colonne contact_id à la table devis
     */
    // public function devis()
    // {
    //     return $this->hasMany(\App\Models\Devis::class, 'contact_id');
    // }

    // ============================================================================
    // SCOPES
    // ============================================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ============================================================================
    // MUTATORS
    // ============================================================================

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
}