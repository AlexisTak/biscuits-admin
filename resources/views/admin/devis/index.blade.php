{{-- resources/views/admin/devis/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Devis')

@section('content')
<div class="devis-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Devis</h1>
            <p class="page-subtitle">{{ $devis->total() }} devis au total</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.devis.index') }}" class="filters-form">
            <div class="filters-grid">
                {{-- Recherche --}}
                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Nom du client..."
                        class="filter-input"
                    >
                </div>

                {{-- Statut --}}
                <div class="filter-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approuvé</option>
                        <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>

                {{-- Service --}}
                <div class="filter-group">
                    <label for="service">Service</label>
                    <select id="service" name="service" class="filter-select">
                        <option value="">Tous les services</option>
                        @foreach($services as $service)
                        <option value="{{ $service }}" {{ ($filters['service'] ?? '') === $service ? 'selected' : '' }}>
                            {{ $service }}
                        </option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('admin.devis.index') }}" class="btn-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devis as $item)
                    <tr>
                        <td class="font-medium">#{{ $item->id }}</td>
                        <td>
                            @if($item->contact)
                                <div>
                                    <div class="font-medium">{{ $item->contact->name }}</div>
                                    <div class="text-muted" style="font-size: 0.8125rem;">{{ $item->contact->email }}</div>
                                </div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $item->service }}</td>
                        <td class="font-medium">{{ number_format($item->amount, 2, ',', ' ') }} €</td>
                        <td>
                            @if($item->status === 'pending')
                                <span class="badge badge-pending">En attente</span>
                            @elseif($item->status === 'approved')
                                <span class="badge badge-approved">Approuvé</span>
                            @else
                                <span class="badge badge-rejected">Rejeté</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.devis.show', $item) }}" class="btn-icon" title="Voir">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                                
                                <a href="{{ route('admin.devis.pdf', $item) }}" class="btn-icon" title="Télécharger le PDF" target="_blank">                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                                        <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                                        <path d="M12 12v6m-3-3h6" stroke-width="2"/>
                                    </svg>
                                </a>
                                
                                <form action="{{ route('admin.devis.destroy', $item) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce devis ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                                        <svg width="26" height="26" fill="none" stroke="currentColor">
                                            <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 3rem;">
                            Aucun devis trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($devis->hasPages())
        <div class="pagination-wrapper">
            {{ $devis->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ... (Le CSS reste inchangé) ... */
.filters-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon-danger:hover {
    color: var(--color-red);
}

.pagination-wrapper {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
}
</style>
@endpush

@endsection