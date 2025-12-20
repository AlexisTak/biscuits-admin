@extends('admin.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="ticket-show">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                🎫 Ticket {{ $ticket->ticket_number }}
            </h1>
            <p class="page-subtitle">
                {{ $ticket->user->name }} ({{ $ticket->user->email }})
                • Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.tickets.index') }}" class="btn-secondary">
                ← Retour
            </a>
        </div>
    </div>

    <div class="ticket-grid">
        {{-- Colonne principale --}}
        <div class="ticket-main">
            
            {{-- Sujet et description --}}
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">{{ $ticket->subject }}</h2>
                    <div class="card-badges">
                        <span class="badge badge-status-{{ str_replace('_', '-', $ticket->status) }}">
                            {{ $ticket->status_label }}
                        </span>
                        <span class="badge badge-priority-{{ $ticket->priority }}">
                            {{ $ticket->priority_label }}
                        </span>
                    </div>
                </div>
                
                <div class="ticket-description">
                    <div class="message-author">
                        <div class="author-avatar">👤</div>
                        <div>
                            <div class="author-name">{{ $ticket->user->name }}</div>
                            <div class="author-time">{{ $ticket->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="message-content">
                        {{ $ticket->description }}
                    </div>
                    
                    {{-- Pièces jointes initiales --}}
                    @if($ticket->attachments->where('ticket_reply_id', null)->count() > 0)
                    <div class="attachments-list">
                        <strong>📎 Pièces jointes :</strong>
                        @foreach($ticket->attachments->where('ticket_reply_id', null) as $attachment)
                        <a href="{{ route('admin.tickets.attachment.download', $attachment) }}" class="attachment-item">
                            <svg width="26" height="26" fill="none" stroke="currentColor">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" stroke-width="2"/>
                            </svg>
                            <span>{{ $attachment->original_filename }}</span>
                            <span class="attachment-size">({{ $attachment->getFormattedSize() }})</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Réponses --}}
            @if($ticket->replies->count() > 0)
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">💬 Réponses ({{ $ticket->replies->count() }})</h3>
                </div>
                
                <div class="replies-list">
                    @foreach($ticket->replies as $reply)
                    <div class="reply-item {{ $reply->is_internal ? 'reply-internal' : '' }}">
                        <div class="message-author">
                            <div class="author-avatar">
                                {{ $reply->isFromCustomer() ? '👤' : '🛠️' }}
                            </div>
                            <div>
                                <div class="author-name">
                                    {{ $reply->user->name }}
                                    @if(!$reply->isFromCustomer())
                                        <span class="badge badge-small">Support</span>
                                    @endif
                                    @if($reply->is_internal)
                                        <span class="badge badge-small badge-warning">Note interne</span>
                                    @endif
                                </div>
                                <div class="author-time">{{ $reply->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="message-content">
                            {{ $reply->message }}
                        </div>
                        
                        {{-- Pièces jointes de la réponse --}}
                        @if($reply->attachments->count() > 0)
                        <div class="attachments-list">
                            <strong>📎 Pièces jointes :</strong>
                            @foreach($reply->attachments as $attachment)
                            <a href="{{ route('admin.tickets.attachment.download', $attachment) }}" class="attachment-item">
                                <svg width="26" height="26" fill="none" stroke="currentColor">
                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" stroke-width="2"/>
                                </svg>
                                <span>{{ $attachment->original_filename }}</span>
                                <span class="attachment-size">({{ $attachment->getFormattedSize() }})</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Formulaire de réponse --}}
            @if($ticket->canBeReplied())
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">✍️ Ajouter une réponse</h3>
                </div>
                
                <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <textarea 
                            name="message" 
                            rows="6" 
                            required
                            maxlength="5000"
                            placeholder="Votre réponse..."
                            class="form-control @error('message') is-invalid @enderror"
                        ></textarea>
                        @error('message')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_internal" value="1">
                            <span>Note interne (invisible pour le client)</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>Changer le statut</label>
                        <select name="change_status" class="form-control">
                            <option value="">Ne pas changer</option>
                            <option value="open">Ouvert</option>
                            <option value="in_progress">En cours</option>
                            <option value="waiting">En attente</option>
                            <option value="resolved">Résolu</option>
                            <option value="closed">Fermé</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg width="26" height="26" fill="none" stroke="currentColor">
                                <line x1="22" y1="2" x2="11" y2="13" stroke-width="2"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2" stroke-width="2"/>
                            </svg>
                            Envoyer la réponse
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info">
                🔒 Ce ticket est fermé et ne peut plus recevoir de réponses.
            </div>
            @endif
        </div>

        {{-- Sidebar actions --}}
        <div class="ticket-sidebar">
            
            {{-- Informations --}}
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">📋 Informations</h3>
                </div>
                
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Statut</span>
                        <span class="badge badge-status-{{ str_replace('_', '-', $ticket->status) }}">
                            {{ $ticket->status_label }}
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Priorité</span>
                        <span class="badge badge-priority-{{ $ticket->priority }}">
                            {{ $ticket->priority_label }}
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Catégorie</span>
                        <span>{{ $ticket->category_label }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Créé le</span>
                        <span>{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    @if($ticket->resolved_at)
                    <div class="info-item">
                        <span class="info-label">Résolu le</span>
                        <span>{{ $ticket->resolved_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    
                    @if($ticket->closed_at)
                    <div class="info-item">
                        <span class="info-label">Fermé le</span>
                        <span>{{ $ticket->closed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Assignation --}}
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">🎯 Assignation</h3>
                </div>
                
                <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <select name="assigned_to" class="form-control" onchange="this.form.submit()">
                            <option value="">Non assigné</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Actions rapides --}}
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">⚡ Actions rapides</h3>
                </div>
                
                <div class="quick-actions">
                    @if(!$ticket->isClosed())
                    <form action="{{ route('admin.tickets.updateStatus', $ticket) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="btn-secondary btn-block">
                            ✅ Marquer comme résolu
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.tickets.updateStatus', $ticket) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="btn-secondary btn-block" onclick="return confirm('Fermer ce ticket ?')">
                            🔒 Fermer le ticket
                        </button>
                    </form>
                    @endif
                    
                    <form action="{{ route('admin.tickets.updatePriority', $ticket) }}" method="POST">
                        @csrf
                        <select name="priority" class="form-control" onchange="this.form.submit()">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Priorité: Faible</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Priorité: Moyenne</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Priorité: Haute</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Priorité: Urgente</option>
                        </select>
                    </form>
                    
                    <form action="{{ route('admin.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Supprimer ce ticket définitivement ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-block">
                            🗑️ Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.ticket-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .ticket-grid {
        grid-template-columns: 1fr;
    }
}

.ticket-description {
    padding: 1.5rem;
    background: var(--bg-primary);
    border-radius: 0.5rem;
}

.message-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.author-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: var(--accent-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.author-name {
    font-weight: 600;
    color: var(--text-primary);
}

.author-time {
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

.message-content {
    color: var(--text-primary);
    line-height: 1.6;
    white-space: pre-wrap;
}

.replies-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reply-item {
    padding: 1.5rem;
    background: var(--bg-primary);
    border-radius: 0.5rem;
    border-left: 3px solid var(--accent-primary);
}

.reply-internal {
    background: rgba(245, 158, 11, 0.05);
    border-left-color: var(--color-orange);
}

.attachments-list {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.attachment-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.375rem;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.2s;
}

.attachment-item:hover {
    border-color: var(--accent-primary);
    background: var(--bg-primary);
}

.attachment-item svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.attachment-size {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.badge-small {
    font-size: 0.6875rem;
    padding: 0.125rem 0.5rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-weight: 500;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
}
</style>
@endpush
@endsection