@extends('admin.layouts.app')

@section('title', 'AI Chat - Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/ai.css') }}">
@endpush

@section('content')
<div class="ai-container">
    
    <!-- Header -->
    <div class="ai-header">
        <h1>🤖 Gestion AI Chat</h1>
        <p>Tableau de bord des conversations et statistiques</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        
        <!-- Total Conversations -->
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Total Conversations</p>
                    <p class="stat-value">{{ number_format($stats['total_conversations']) }}</p>
                </div>
            </div>
            <p class="stat-change">+{{ $stats['conversations_today'] }} aujourd'hui</p>
        </div>

        <!-- Total Messages -->
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Total Messages</p>
                    <p class="stat-value">{{ number_format($stats['total_messages']) }}</p>
                </div>
            </div>
            <p class="stat-change">+{{ $stats['messages_today'] }} aujourd'hui</p>
        </div>

        <!-- Avg Messages -->
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Moy. Messages</p>
                    <p class="stat-value">{{ $stats['avg_messages_per_conversation'] }}</p>
                </div>  
            </div>
            <p class="stat-label mt-4">Par conversation</p>
        </div>

        <!-- By Assistant -->
        <div class="stat-card">
            <div class="stat-header">
                <p class="stat-label">Par Assistant</p>
            </div>
            <div class="stat-list">
                @foreach($stats['by_assistant'] as $assistant => $count)
                <div class="stat-item">
                    <span>{{ ucfirst($assistant) }}</span>
                    <span>{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="{{ route('admin.ai.conversations') }}" class="action-card">
            <div class="action-icon blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <div class="action-content">
                <h3>Voir les conversations</h3>
                <p>Liste complète</p>
            </div>
        </a>

        <a href="{{ route('admin.ai.stats') }}" class="action-card">
            <div class="action-icon purple">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="action-content">
                <h3>Statistiques avancées</h3>
                <p>Graphiques détaillés</p>
            </div>
        </a>

        <a href="{{ route('admin.ai.export') }}" class="action-card">
            <div class="action-icon green">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="action-content">
                <h3>Exporter les données</h3>
                <p>CSV / JSON</p>
            </div>
        </a>
    </div>

    <!-- Recent Conversations -->
    <div class="card">
        <div class="card-header">
            <h2>Conversations récentes</h2>
        </div>
        
        @forelse($recentConversations as $conv)
        <div class="conversation-item">
            <div class="conversation-actions">
                <div class="flex-1">
                    <div class="conversation-header">
                        <span class="badge {{ $conv->assistant }}">
                            {{ ucfirst($conv->assistant) }}
                        </span>
                        <span class="conversation-meta">{{ $conv->messages_count }} messages</span>
                        <span class="conversation-meta">{{ $conv->created_at->diffForHumans() }}</span>
                    </div>
                    @if($conv->messages->first())
                    <p class="conversation-content">
                        {{ Str::limit($conv->messages->first()->content, 150) }}
                    </p>
                    @endif
                </div>
                <a href="{{ route('admin.ai.show', $conv) }}" class="btn btn-ghost btn-sm">
                    Voir
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">💬</div>
            <p>Aucune conversation pour le moment</p>
        </div>
        @endforelse
        
        <div class="card-footer">
            <a href="{{ route('admin.ai.conversations') }}" class="btn btn-ghost">
                Voir toutes les conversations →
            </a>
        </div>
    </div>

</div>
@endsection