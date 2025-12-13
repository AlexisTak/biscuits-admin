{{-- resources/views/admin/users/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="users-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Utilisateurs</h1>
            <p class="page-subtitle">{{ $users->total() }} utilisateur(s) au total</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <line x1="12" y1="5" x2="12" y2="19" stroke-width="2"/>
                    <line x1="5" y1="12" x2="19" y2="12" stroke-width="2"/>
                </svg>
                Nouvel utilisateur
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filters-form">
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

                {{-- Rôle --}}
                <div class="filter-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="filter-select">
                        <option value="">Tous les rôles</option>
                        <option value="admin" {{ ($filters['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ ($filters['role'] ?? '') === 'user' ? 'selected' : '' }}>User</option>
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
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Réinitialiser</a>
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
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="font-medium">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge badge-approved">Admin</span>
                            @else
                                <span class="badge badge-pending">User</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-icon" title="Voir">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-icon" title="Modifier">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-width="2"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2"/>
                                    </svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                                        <svg width="26" height="26" fill="none" stroke="currentColor">
                                            <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
        @endif
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

.pagination-wrapper {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
}
</style>
@endpush

@endsection