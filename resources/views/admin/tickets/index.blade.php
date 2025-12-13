@extends('layouts.app')

@section('title', 'Mes Tickets')

@section('content')
<div class="tickets-container">
    
    <!-- Header -->
    <div class="tickets-header">
        <div>
            <h1>🎫 Mes Tickets de Support</h1>
            <p>Gérez vos demandes de support</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="btn-ticket btn-ticket-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouveau ticket
        </a>
    </div>

    <!-- Filtres -->
    <div class="tickets-filters">
        <form method="GET">
            <div>
                <label>Statut</label>
                <select name="status">
                    <option value="all">Tous</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>En attente</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                </select>
            </div>

            <div>
                <label>Priorité</label>
                <select name="priority">
                    <option value="all">Toutes</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>

            <div>
                <label>Recherche</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Rechercher...">
            </div>

            <div class="tickets-filters-actions">
                <button type="submit" class="btn-ticket btn-ticket-primary">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des tickets -->
    <div class="tickets-list">
        @forelse($tickets as $ticket)
        <div class="ticket-card">
            <div class="ticket-card-header">
                <span class="ticket-number">{{ $ticket->ticket_number }}</span>
                
                <span class="ticket-badge status-{{ str_replace('_', '-', $ticket->status) }}">
                    {{ $ticket->status_label }}
                </span>
                
                <span class="ticket-badge priority-{{ $ticket->priority }}">
                    {{ $ticket->priority_label }}
                </span>

                <span style="font-size: 0.875rem; color: #9ca3af;">
                    {{ $ticket->replies_count }} réponse(s)
                </span>
            </div>

            <h3 class="ticket-subject">{{ $ticket->subject }}</h3>

            <p class="ticket-description">{{ Str::limit($ticket->description, 200) }}</p>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1rem;">
                <div class="ticket-meta">
                    <span>Créé {{ $ticket->created_at->diffForHumans() }}</span>
                    @if($ticket->assigned_to)
                    <span>Assigné à {{ $ticket->assignedTo->name }}</span>
                    @endif
                </div>

                <a href="{{ route('tickets.show', $ticket) }}" class="btn-ticket btn-ticket-ghost">
                    Voir détails
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">🎫</div>
            <h3>Aucun ticket</h3>
            <p>Vous n'avez pas encore créé de ticket de support</p>
            <a href="{{ route('tickets.create') }}" class="btn-ticket btn-ticket-primary">
                Créer mon premier ticket
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
    <div style="margin-top: 2rem;">
        {{ $tickets->links() }}
    </div>
    @endif

</div>
@endsection