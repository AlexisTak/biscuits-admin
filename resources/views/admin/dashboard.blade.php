@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="admin-dashboard">
    {{-- Header --}}
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Vue d'ensemble de votre activité</p>
        </div>
        <div class="header-actions">
            <button onclick="refreshStats()" class="btn-secondary">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <path d="M23 4v6h-6M1 20v-6h6" stroke-width="2"/>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" stroke-width="2"/>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        {{-- Total Contacts --}}
        <div class="stat-card">
            <div class="stat-icon stat-blue">
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                    <circle cx="9" cy="7" r="4" stroke-width="2"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Contacts</p>
                <p class="stat-value">{{ number_format($stats['totalContacts']) }}</p>
                <p class="stat-change stat-change-positive">
                    +{{ $stats['newContactsThisMonth'] }} ce mois
                </p>
            </div>
        </div>

        {{-- Total Devis --}}
        <div class="stat-card">
            <div class="stat-icon stat-green">
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                    <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Devis</p>
                <p class="stat-value">{{ number_format($stats['totalDevis']) }}</p>
                <p class="stat-detail">
                    {{ $stats['approvedDevis'] }} approuvés
                </p>
            </div>
        </div>

        {{-- Devis en attente --}}
        <div class="stat-card">
            <div class="stat-icon stat-yellow">
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">En attente</p>
                <p class="stat-value">{{ number_format($stats['pendingDevis']) }}</p>
                <a href="{{ route('admin.devis.index', ['status' => 'pending']) }}" class="stat-link">
                    Voir tout →
                </a>
            </div>
        </div>

        {{-- Revenus --}}
        <div class="stat-card">
            <div class="stat-icon stat-purple">
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Revenus</p>
                <p class="stat-value">{{ number_format($stats['revenue'], 2, ',', ' ') }} €</p>
                <p class="stat-detail">
                    Devis approuvés
                </p>
            </div>
        </div>
    </div>

    {{-- Tables --}}
    <div class="dashboard-tables">
        {{-- Contacts récents --}}
        <div class="table-card">
            <div class="table-card-header">
                <h2>Contacts récents</h2>
                <a href="{{ route('admin.contacts.index') }}" class="btn-text">
                    Voir tout →
                </a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recentContacts'] as $contact)
                        <tr>
                            <td class="font-medium">{{ $contact['name'] }}</td>
                            <td>{{ $contact['email'] }}</td>
                            <td>
                                <span class="badge badge-{{ $contact['status'] }}">
                                    {{ ucfirst($contact['status']) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $contact['created_at'] }}</td>
                            <td>
                                <a href="{{ route('admin.contacts.show', $contact['id']) }}" class="btn-icon" title="Voir détails">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun contact récent</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Devis récents --}}
        <div class="table-card">
            <div class="table-card-header">
                <h2>Devis récents</h2>
                <a href="{{ route('admin.devis.index') }}" class="btn-text">
                    Voir tout →
                </a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recentDevis'] as $devis)
                        <tr>
                            <td class="font-medium">{{ $devis['contact_name'] }}</td>
                            <td>{{ $devis['service'] }}</td>
                            <td class="font-medium">{{ number_format($devis['amount'], 2, ',', ' ') }} €</td>
                            <td>
                                <span class="badge badge-{{ $devis['status'] }}">
                                    {{ ucfirst($devis['status']) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $devis['created_at'] }}</td>
                            <td>
                                <a href="{{ route('admin.devis.show', $devis['id']) }}" class="btn-icon" title="Voir détails">
                                    <svg width="26" height="26" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun devis récent</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Activité récente --}}
    <div class="activity-card">
        <div class="activity-card-header">
            <h2>Activité récente</h2>
            <a href="{{ route('admin.activity-logs') }}" class="btn-text">
                Voir tout →
            </a>
        </div>
        <div class="activity-list">
            @forelse($recentActivity as $activity)
            <div class="activity-item">
                <div class="activity-icon activity-icon-{{ $activity['type'] }}">
                    @switch($activity['type'])
                        @case('contact')
                            <svg width="26" height="26" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                                <circle cx="9" cy="7" r="4" stroke-width="2"/>
                            </svg>
                            @break
                        @case('devis')
                            <svg width="26" height="26" fill="none" stroke="currentColor">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                                <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                            </svg>
                            @break
                        @default
                            <svg width="26" height="26" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            </svg>
                    @endswitch
                </div>
                <div class="activity-content">
                    <p class="activity-description">
                        <strong>{{ $activity['user_name'] }}</strong>
                        {{ $activity['description'] }}
                    </p>
                    <p class="activity-time">{{ $activity['created_at'] }}</p>
                </div>
            </div>
            @empty
            <p class="text-center text-muted">Aucune activité récente</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshStats() {
    // Rafraîchir la page pour recharger les stats
    window.location.reload();
}
</script>
@endpush

@endsection