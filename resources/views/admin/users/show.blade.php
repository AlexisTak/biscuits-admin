@extends('admin.layouts.app')

@section('title', 'Utilisateur - ' . $user->name)

@section('content')
<div class="user-show-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $user->name }}</h1>
            <p class="page-subtitle">{{ $user->email }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-width="2"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2"/>
                </svg>
                Modifier
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">← Retour</a>
        </div>
    </div>

    {{-- Informations --}}
    <div class="info-card">
        <h2 class="card-title">Informations</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nom</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Rôle</span>
                <span class="info-value">
                    @if($user->role === 'admin')
                        <span class="badge badge-approved">Admin</span>
                    @else
                        <span class="badge badge-pending">User</span>
                    @endif
                </span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email vérifié</span>
                <span class="info-value">
                    @if($user->email_verified_at)
                        <span class="badge badge-approved">✓ Vérifié</span>
                    @else
                        <span class="badge badge-pending">Non vérifié</span>
                    @endif
                </span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Créé le</span>
                <span class="info-value">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Mis à jour le</span>
                <span class="info-value">{{ $user->updated_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- Actions dangereuses --}}
    @if($user->id !== auth()->id())
    <div class="danger-zone">
        <h3>Zone de danger</h3>
        <p>Ces actions sont irréversibles.</p>
        
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke-width="2"/>
                </svg>
                Supprimer l'utilisateur
            </button>
        </form>
    </div>
    @endif
</div>

@push('styles')
<style>
.info-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 2rem;
    margin-bottom: 1.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: var(--text-primary);
}

.danger-zone {
    background: rgba(239, 68, 68, 0.05);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 0.75rem;
    padding: 2rem;
}

.danger-zone h3 {
    color: var(--color-red);
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.danger-zone p {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}
</style>
@endpush

@endsection