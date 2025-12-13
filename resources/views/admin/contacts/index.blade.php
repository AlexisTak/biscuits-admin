@extends('admin.layouts.app')

@section('title', 'Contacts')

@section('content')
<div class="contacts-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Contacts</h1>
            <p class="page-subtitle">{{ $contacts->total() }} contact(s) au total</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.contacts.index') }}" class="filters-form">
            <div class="filters-grid">
                {{-- Recherche --}}
                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Nom ou email..."
                        class="filter-input"
                    >
                </div>

                {{-- Statut --}}
                <div class="filter-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="processed" {{ ($filters['status'] ?? '') === 'processed' ? 'selected' : '' }}>Traité</option>
                        <option value="archived" {{ ($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>Archivé</option>
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
                    <a href="{{ route('admin.contacts.index') }}" class="btn-secondary">Réinitialiser</a>
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
                        <th>
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Pays</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td>
                            <input type="checkbox" class="contact-checkbox" value="{{ $contact->id }}">
                        </td>
                        <td class="font-medium">{{ $contact->name }}</td>
                        <td>{{ $contact->email }}</td>
                        <td>{{ $contact->country }}</td>
                        <td>{{ $contact->phone }}</td>
                        <td>
                            <span class="badge badge-{{ $contact->status }}">
                                {{ ucfirst($contact->status) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.contacts.show', $contact) }}" class="btn-icon" title="Voir">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce contact ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                                        <svg width="26" height="26" fill="none" stroke="currentColor">
                                            <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 3rem;">
                            Aucun contact trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($contacts->hasPages())
        <div class="pagination-wrapper">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>

    {{-- Actions en masse --}}
    <div class="bulk-actions" id="bulkActions" style="display: none;">
        <div class="bulk-actions-content">
            <span id="selectedCount">0</span> contact(s) sélectionné(s)
            <button type="button" class="btn-danger" onclick="bulkDelete()">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke-width="2"/>
                </svg>
                Supprimer la sélection
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
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

.bulk-actions {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
}

.bulk-actions-content {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-lg);
}

.pagination-wrapper {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
}
</style>
@endpush

@push('scripts')
<script>
// Sélection multiple
const selectAll = document.getElementById('select-all');
const checkboxes = document.querySelectorAll('.contact-checkbox');
const bulkActions = document.getElementById('bulkActions');
const selectedCount = document.getElementById('selectedCount');

selectAll?.addEventListener('change', function() {
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = Array.from(checkboxes).filter(cb => cb.checked);
    selectedCount.textContent = selected.length;
    bulkActions.style.display = selected.length > 0 ? 'block' : 'none';
}

function bulkDelete() {
    const selected = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
    
    if (selected.length === 0) return;
    
    if (!confirm(`Supprimer ${selected.length} contact(s) ?`)) return;
    
    fetch('{{ route("admin.contacts.bulk-delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erreur lors de la suppression');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur réseau');
    });
}
</script>
@endpush

@endsection