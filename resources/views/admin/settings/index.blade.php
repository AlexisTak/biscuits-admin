@extends('admin.layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="settings-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Paramètres</h1>
            <p class="page-subtitle">Configuration et maintenance de l'application</p>
        </div>
    </div>

    {{-- Informations système --}}
    <div class="settings-card">
        <h2 class="card-title">Informations système</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Version Laravel</span>
                <span class="info-value">{{ $stats['laravel_version'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Version PHP</span>
                <span class="info-value">{{ $stats['php_version'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Taille du cache</span>
                <span class="info-value">{{ $stats['cache_size'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Taille des logs</span>
                <span class="info-value">{{ $stats['logs_size'] }}</span>
            </div>
        </div>
    </div>

    {{-- Actions de maintenance --}}
    <div class="settings-card">
        <h2 class="card-title">Actions de maintenance</h2>
        
        <div class="actions-grid">
            {{-- Vider le cache --}}
            <div class="action-card">
                <div class="action-icon action-icon-blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <polyline points="23 4 23 10 17 10" stroke-width="2"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke-width="2"/>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Vider le cache</h3>
                    <p>Supprime tous les caches (config, routes, vues)</p>
                </div>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="clear_cache">
                    <button type="submit" class="btn-secondary" onclick="return confirm('Vider le cache ?')">
                        Exécuter
                    </button>
                </form>
            </div>

            {{-- Supprimer les logs --}}
            <div class="action-card">
                <div class="action-icon action-icon-yellow">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                        <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Supprimer les logs</h3>
                    <p>Supprime tous les fichiers de logs anciens</p>
                </div>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn-secondary" onclick="return confirm('Supprimer les logs ?')">
                        Exécuter
                    </button>
                </form>
            </div>

            {{-- Optimiser l'application --}}
            <div class="action-card">
                <div class="action-icon action-icon-green">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke-width="2"/>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Optimiser</h3>
                    <p>Cache les configurations pour de meilleures performances</p>
                </div>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="optimize">
                    <button type="submit" class="btn-primary">
                        Exécuter
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Informations de sécurité --}}
    <div class="settings-card">
        <h2 class="card-title">Sécurité</h2>
        
        <div class="security-info">
            <div class="alert" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981;">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <path d="M20 6L9 17l-5-5" stroke-width="2"/>
                </svg>
                <div>
                    <strong>Application sécurisée</strong>
                    <p style="margin-top: 0.25rem; font-size: 0.875rem;">
                        Les protections CSRF, XSS et SQL Injection sont actives.
                    </p>
                </div>
            </div>

            <div class="security-tips">
                <h4>Bonnes pratiques :</h4>
                <ul>
                    <li>Changez régulièrement les mots de passe administrateurs</li>
                    <li>Activez l'authentification à deux facteurs en production</li>
                    <li>Surveillez les logs d'activité régulièrement</li>
                    <li>Maintenez Laravel et PHP à jour</li>
                    <li>Utilisez HTTPS en production</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.settings-card {
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
}

.info-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.actions-grid {
    display: grid;
    gap: 1.5rem;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    transition: var(--transition);
}

.action-card:hover {
    border-color: var(--border-hover);
}

.action-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.action-icon-blue {
    background: rgba(59, 130, 246, 0.1);
    color: var(--color-blue);
}

.action-icon-green {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-green);
}

.action-icon-yellow {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-yellow);
}

.action-content {
    flex: 1;
}

.action-content h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.action-content p {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.security-info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.security-tips h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
}

.security-tips ul {
    list-style: none;
    padding: 0;
}

.security-tips li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
    position: relative;
}

.security-tips li::before {
    content: "•";
    position: absolute;
    left: 0;
    color: var(--accent-primary);
    font-weight: bold;
}

.alert {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
}

.alert svg {
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .action-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endpush

@endsection