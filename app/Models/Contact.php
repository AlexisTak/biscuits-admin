<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'ip_address',
        'user_agent',
        'fingerprint',
    ];

    /**
     * Scopes
     */
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

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessors
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '🟡 En attente',
            'processed' => '🟢 Traité',
            'archived' => '⚪ Archivé',
            default => '⚫ Inconnu',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'high' => '🔴 Haute',
            'normal' => '🟠 Normale',
            'low' => '🟢 Basse',
            default => '⚪ Non définie',
        };
    }

    /**
     * Mutators
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
}