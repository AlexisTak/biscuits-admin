{{-- ============================================================================
   FICHIER 1 : resources/views/admin/tickets/index.blade.php
   ============================================================================ --}}
@extends('admin.layouts.app')

@section('title', 'Gestion des Tickets')

@section('content')
<div class="tickets-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">🎫 Gestion des Tickets</h1>
            <p class="page-subtitle">{{ $stats['total'] }} ticket(s) au total</p>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">🟢</div>
            <div class="stat-content">
                <div class="stat-label">Ouverts</div>
                <div class="stat-value">{{ $stats['open'] }}</div>
            </div>
        </div>
        
        <div class="stat-card stat-info">
            <div class="stat-icon">🔵</div>
            <div class="stat-content">
                <div class="stat-label">En cours</div>
                <div class="stat-value">{{ $stats['in_progress'] }}</div>
            </div>
        </div>
        
        <div class="stat-card stat-danger">
            <div class="stat-icon">🔴</div>
            <div class="stat-content">
                <div class="stat-label">Urgents</div>
                <div class="stat-value">{{ $stats['urgent'] }}</div>
            </div>
        </div>
        
        <div class="stat-card stat-purple">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-label">Résolus (24h)</div>
                <div class="stat-value">{{ $stats['resolved_today'] }}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-content">
                <div class="stat-label">Temps moyen</div>
                <div class="stat-value">{{ $stats['avg_response_time'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="filters-form">
            <div class="filters-grid">
                {{-- Statut --}}
                <div class="filter-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="all">Tous</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>En attente</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                    </select>
                </div>

                {{-- Priorité --}}
                <div class="filter-group">
                    <label for="priority">Priorité</label>
                    <select id="priority" name="priority" class="filter-select">
                        <option value="all">Toutes</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                {{-- Assigné à --}}
                <div class="filter-group">
                    <label for="assigned_to">Assigné à</label>
                    <select id="assigned_to" name="assigned_to" class="filter-select">
                        <option value="all">Tous</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Non assigné</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Recherche --}}
                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Numéro, sujet, client..."
                        class="filter-input"
                    >
                </div>

                {{-- Boutons --}}
                <div class="filter-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8" stroke-width="2"/>
                            <path d="m21 21-4.35-4.35" stroke-width="2"/>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.tickets.index') }}" class="btn-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tableau des tickets --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Sujet</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th>Priorité</th>
                        <th>Assigné à</th>
                        <th>Réponses</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="font-medium">
                            <span class="ticket-number">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td>
                            <div class="ticket-subject">{{ Str::limit($ticket->subject, 50) }}</div>
                            <div class="ticket-category">{{ $ticket->category_label }}</div>
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-name">{{ $ticket->user->name }}</div>
                                <div class="user-email">{{ $ticket->user->email }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-status-{{ str_replace('_', '-', $ticket->status) }}">
                                {{ $ticket->status_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-priority-{{ $ticket->priority }}">
                                {{ $ticket->priority_label }}
                            </span>
                        </td>
                        <td>
                            @if($ticket->assignedTo)
                                <span class="assigned-user">{{ $ticket->assignedTo->name }}</span>
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="replies-count">{{ $ticket->replies_count }}</span>
                        </td>
                        <td class="text-muted">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn-icon" title="Voir">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                                
                                @if(!$ticket->assigned_to)
                                <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                    <button type="submit" class="btn-icon btn-icon-primary" title="M'assigner">
                                        <svg width="26" height="26" fill="none" stroke="currentColor">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                                            <circle cx="8.5" cy="7" r="4" stroke-width="2"/>
                                            <line x1="20" y1="8" x2="20" y2="14" stroke-width="2"/>
                                            <line x1="23" y1="11" x2="17" y2="11" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 3rem;">
                            Aucun ticket trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="pagination-wrapper">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card:hover {
    border-color: var(--accent-primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    font-size: 2rem;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-success { border-color: var(--color-green); }
.stat-info { border-color: var(--color-blue); }
.stat-danger { border-color: var(--color-red); }
.stat-purple { border-color: var(--color-purple); }

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--accent-primary);
}

.ticket-subject {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.ticket-category {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.user-name {
    font-weight: 500;
    color: var(--text-primary);
}

.user-email {
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

.assigned-user {
    color: var(--text-primary);
    font-weight: 500;
}

.replies-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0.375rem;
    font-weight: 600;
    color: var(--text-primary);
}

.badge-status-open {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-status-in-progress {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.badge-status-waiting {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-status-resolved {
    background: rgba(168, 85, 247, 0.1);
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.badge-status-closed {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
    border: 1px solid rgba(107, 114, 128, 0.3);
}

.badge-priority-low {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.badge-priority-medium {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-priority-high {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-priority-urgent {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.btn-icon-primary {
    color: var(--accent-primary);
}

.btn-icon-primary:hover {
    background: rgba(var(--accent-rgb), 0.1);
}
</style>
@endpush
@endsection