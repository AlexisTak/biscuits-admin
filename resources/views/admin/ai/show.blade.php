@extends('admin.layouts.app')

@section('title', 'Conversation #' . $conversation->id)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/ai.css') }}">
@endpush

@section('content')
<div class="ai-container">
    
    <!-- Header -->
    <div class="ai-header-actions">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h1 style="margin: 0;">Conversation #{{ $conversation->id }}</h1>
                <span class="badge {{ $conversation->assistant }}">
                    {{ ucfirst($conversation->assistant) }}
                </span>
            </div>
            <p style="color: #6b7280; margin: 0;">
                Créée le {{ $conversation->created_at->format('d/m/Y à H:i') }}
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.ai.conversations') }}" class="btn btn-secondary">
                ← Retour
            </a>
            <form method="POST" action="{{ route('admin.ai.destroy', $conversation) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette conversation ?')" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑️ Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
            <p class="stat-label">Total Messages</p>
            <p class="stat-value" style="font-size: 1.75rem;">
                {{ $stats['total_messages'] }}
            </p>
        </div>
        
        <div class="stat-card">
            <p class="stat-label">Messages Utilisateur</p>
            <p class="stat-value" style="font-size: 1.75rem; color: #2563eb;">
                {{ $stats['user_messages'] }}
            </p>
        </div>
        
        <div class="stat-card">
            <p class="stat-label">Messages Assistant</p>
            <p class="stat-value" style="font-size: 1.75rem; color: #9333ea;">
                {{ $stats['assistant_messages'] }}
            </p>
        </div>
        
        <div class="stat-card">
            <p class="stat-label">Taille moy. réponse</p>
            <p class="stat-value" style="font-size: 1.75rem; color: #10b981;">
                {{ $stats['avg_response_length'] }}
            </p>
        </div>
    </div>

    <!-- Messages -->
    <div class="card">
        <div class="card-header">
            <h2>Historique des messages</h2>
        </div>

        <div class="messages-container">
            @foreach($conversation->messages as $message)
            <div class="message {{ $message->role }}">
                
                <!-- Avatar -->
                <div class="message-avatar">
                    {{ $message->role === 'user' ? '👤' : '🤖' }}
                </div>

                <!-- Message Content -->
                <div class="message-content">
                    <!-- Header -->
                    <div class="message-header">
                        <span class="message-author">
                            {{ $message->role === 'user' ? 'Utilisateur' : 'Assistant' }}
                        </span>
                        <span class="message-time">
                            {{ $message->created_at->format('H:i:s') }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="message-bubble">
                        {{ $message->content }}
                    </div>
                    
                    <!-- Stats -->
                    <div class="message-meta">
                        {{ strlen($message->content) }} caractères
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer Info -->
        <div class="card-footer" style="background: #f9fafb;">
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem; color: #6b7280;">
                <div>
                    <strong>User ID:</strong> {{ $conversation->user_id ?? 'Anonyme' }}
                </div>
                <div>
                    <strong>Dernière activité:</strong> {{ $conversation->updated_at->diffForHumans() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Metadata -->
    @if($conversation->meta && count($conversation->meta) > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h2>Métadonnées</h2>
        </div>
        <div class="card-body">
            <div class="code-display">
                <pre>{{ json_encode($conversation->meta, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection