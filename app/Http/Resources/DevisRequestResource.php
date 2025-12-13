<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevisRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'service' => $this->service,
            'budget' => $this->budget,
            'message' => $this->message,
            
            // Statut
            'status' => $this->status,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            
            // Devis
            'quoted_amount' => $this->quoted_amount,
            'quote_details' => $this->quote_details,
            'quoted_at' => $this->quoted_at?->toIso8601String(),
            
            // Métadonnées
            'ip_address' => $this->when($request->user()?->isAdmin(), $this->ip_address),
            
            // Dates
            'read_at' => $this->read_at?->toIso8601String(),
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