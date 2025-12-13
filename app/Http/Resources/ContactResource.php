<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country' => $this->country,
            'service' => $this->service,
            'message' => $this->message,
            
            // Statut
            'status' => $this->status,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            'tags' => $this->tags ?? [],
            
            // Métadonnées
            'ip_address' => $this->when($request->user()?->isAdmin(), $this->ip_address),
            'fingerprint' => $this->when($request->user()?->isAdmin(), $this->fingerprint),
            
            // Dates
            'read_at' => $this->read_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Relations
            'assigned_to' => $this->whenLoaded('assignedUser', fn() => [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
            ]),
            
            'responses' => ContactResponseResource::collection($this->whenLoaded('responses')),
            'responses_count' => $this->whenCounted('responses'),
        ];
    }
}