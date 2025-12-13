{{-- resources/views/admin/devis/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Devis #' . $devis->id)

@section('content')
<div class="devis-show">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Devis #{{ $devis->id }}</h1>
            <p class="page-subtitle">
                @if($devis->contact)
                    {{ $devis->contact->name }} ({{ $devis->contact->email }})
                @else
                    Contact supprimé
                @endif
                • {{ $devis->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.devis.index') }}" class="btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="devis-grid">
        {{-- Informations principales --}}
        <div class="info-card">
            <div class="card-header">
                <h2 class="card-title">Informations du devis</h2>
                <span class="badge badge-{{ $devis->status }}">
                    @switch($devis->status)
                        @case('pending') En attente @break
                        @case('approved') Approuvé @break
                        @case('rejected') Rejeté @break
                        @default {{ ucfirst($devis->status) }}
                    @endswitch
                </span>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="26" height="26" rx="2" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="9" stroke-width="2"/>
                            <line x1="9" y1="15" x2="15" y2="15" stroke-width="2"/>
                        </svg>
                        Numéro de devis
                    </span>
                    <span class="info-value">#{{ str_pad($devis->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                @if($devis->contact)
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                            <circle cx="9" cy="7" r="4" stroke-width="2"/>
                        </svg>
                        Client
                    </span>
                    <span class="info-value">
                        <a href="{{ route('admin.contacts.show', $devis->contact) }}" class="client-link">
                            {{ $devis->contact->name }}
                        </a>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <rect x="2" y="4" width="20" height="16" rx="2" stroke-width="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" stroke-width="2"/>
                        </svg>
                        Email
                    </span>
                    <span class="info-value">
                        <a href="mailto:{{ $devis->contact->email }}" class="email-link">
                            {{ $devis->contact->email }}
                        </a>
                    </span>
                </div>
                @endif
                
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                            <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                        </svg>
                        Service demandé
                    </span>
                    <span class="info-value">{{ $devis->service }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/>
                        </svg>
                        Montant
                    </span>
                    <span class="info-value amount-value">{{ number_format($devis->amount, 2, ',', ' ') }} €</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                        </svg>
                        Date de création
                    </span>
                    <span class="info-value">{{ $devis->created_at->format('d/m/Y à H:i') }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                        </svg>
                        Dernière mise à jour
                    </span>
                    <span class="info-value">{{ $devis->updated_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            @if($devis->notes)
            <div class="notes-section">
                <h3>Notes</h3>
                <div class="notes-content">{{ $devis->notes }}</div>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="actions-card">
            <h3 class="card-title">Actions</h3>

            {{-- Changer le statut --}}
            <div class="action-group">
                <h4>Statut</h4>
                <form action="{{ route('admin.devis.update-status', $devis) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="pending" {{ $devis->status === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ $devis->status === 'approved' ? 'selected' : '' }}>Approuvé</option>
                        <option value="rejected" {{ $devis->status === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </form>
            </div>

            {{-- Modifier le montant --}}
            <div class="action-group">
                <h4>Modifier le montant</h4>
                <form action="{{ route('admin.devis.update-amount', $devis) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01" 
                        min="0" 
                        value="{{ $devis->amount }}" 
                        placeholder="0.00"
                        class="amount-input"
                        required
                    >
                    <button type="submit" class="action-btn action-btn-secondary">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-width="2"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2"/>
                        </svg>
                        Mettre à jour
                    </button>
                </form>
            </div>

            {{-- Envoyer par email --}}
            @if($devis->contact)
            <div class="action-group">
                <h4>Envoyer le devis</h4>
                <a href="mailto:{{ $devis->contact->email }}?subject=Devis #{{ $devis->id }} - {{ $devis->service }}&body=Bonjour {{ $devis->contact->name }},%0D%0A%0D%0AVeuillez trouver ci-joint votre devis pour le service: {{ $devis->service }}%0D%0AMontant: {{ number_format($devis->amount, 2, ',', ' ') }} €" class="action-btn action-btn-primary">
                    <svg width="26" height="26" fill="none" stroke="currentColor">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-width="2"/>
                        <polyline points="22,6 12,13 2,6" stroke-width="2"/>
                    </svg>
                    Envoyer par email
                </a>
            </div>
            @endif

            {{-- Actions rapides --}}
            <div class="action-group">
                <h4>Actions rapides</h4>
                
                @if($devis->status === 'pending')
                <form action="{{ route('admin.devis.update-status', $devis) }}" method="POST" style="margin-bottom: 0.5rem;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="action-btn action-btn-success">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                        </svg>
                        Approuver
                    </button>
                </form>

                <form action="{{ route('admin.devis.update-status', $devis) }}" method="POST" style="margin-bottom: 0.5rem;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="action-btn action-btn-warning">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>
                        </svg>
                        Refuser
                    </button>
                </form>
                @endif

                {{-- <a href="{{ route('admin.devis.pdf', $devis) }}" class="action-btn action-btn-secondary" target="_blank">
                    <svg width="26" height="26" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                        <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                    </svg>
                    Télécharger PDF
                </a> --}}
            </div>

            {{-- Zone de danger --}}
            <div class="danger-zone">
                <h4>Zone de danger</h4>
                <form action="{{ route('admin.devis.destroy', $devis) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn action-btn-danger">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2"/>
                        </svg>
                        Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.devis-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.5rem;
}

.info-card,
.actions-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 2rem;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: var(--text-primary);
    font-weight: 500;
}

.amount-value {
    font-size: 1.5rem;
    color: var(--accent-primary);
    font-weight: 700;
}

.client-link,
.email-link {
    color: var(--accent-primary);
    text-decoration: none;
    transition: var(--transition);
}

.client-link:hover,
.email-link:hover {
    text-decoration: underline;
}

.notes-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.notes-section h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.notes-content {
    background: var(--bg-tertiary);
    padding: 1.5rem;
    border-radius: 0.5rem;
    color: var(--text-secondary);
    line-height: 1.6;
    white-space: pre-wrap;
}

.action-group {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.action-group:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.action-group h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-select,
.amount-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.9375rem;
    transition: var(--transition);
    margin-bottom: 0.75rem;
}

.status-select {
    cursor: pointer;
}

.status-select:focus,
.amount-input:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.action-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: var(--transition);
    text-decoration: none;
}

.action-btn-primary {
    background: var(--accent-primary);
    color: white;
}

.action-btn-primary:hover {
    background: var(--accent-hover);
}

.action-btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.action-btn-secondary:hover {
    background: var(--bg-hover);
}

.action-btn-success {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-green);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.action-btn-success:hover {
    background: rgba(16, 185, 129, 0.2);
}

.action-btn-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-yellow);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.action-btn-warning:hover {
    background: rgba(245, 158, 11, 0.2);
}

.action-btn-danger {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-red);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.action-btn-danger:hover {
    background: rgba(239, 68, 68, 0.2);
}

.danger-zone {
    padding-top: 2rem;
    border-top: 1px solid rgba(239, 68, 68, 0.2);
}

.danger-zone h4 {
    color: var(--color-red);
}

@media (max-width: 1024px) {
    .devis-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@endsection