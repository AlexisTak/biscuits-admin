@extends('admin.layouts.app')

@section('title', 'AI Chat - Conversations')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/ai.css') }}">
@endpush

@section('content')
<div class="ai-container">
    
    <!-- Header -->
    <div class="ai-header-actions">
        <div>
            <h1 class="mb-2">Conversations</h1>
            <p style="color: #6b7280; margin: 0;">Gérez toutes les conversations AI</p>
        </div>
        <a href="{{ route('admin.ai.index') }}" class="btn btn-secondary">
            ← Retour
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-form">
        
        <!-- Search -->
        <div class="form-group" style="grid-column: span 2;">
            <label>Recherche</label>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Rechercher dans les messages..."
                   class="form-control">
        </div>

        <!-- Assistant -->
        <div class="form-group">
            <label>Assistant</label>
            <select name="assistant" class="form-control">
                <option value="all">Tous</option>
                <option value="support" {{ request('assistant') === 'support' ? 'selected' : '' }}>Support</option>
                <option value="dev" {{ request('assistant') === 'dev' ? 'selected' : '' }}>Dev</option>
                <option value="sales" {{ request('assistant') === 'sales' ? 'selected' : '' }}>Sales</option>
            </select>
        </div>

        <!-- Date From -->
        <div class="form-group">
            <label>Date début</label>
            <input type="date" 
                   name="date_from" 
                   value="{{ request('date_from') }}"
                   class="form-control">
        </div>

        <!-- Date To -->
        <div class="form-group">
            <label>Date fin</label>
            <input type="date" 
                   name="date_to" 
                   value="{{ request('date_to') }}"
                   class="form-control">
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                🔍 Filtrer
            </button>
            <a href="{{ route('admin.ai.conversations') }}" class="btn btn-secondary">
                Réinitialiser
            </a>
            <button type="button" onclick="bulkDelete()" class="btn btn-danger ml-auto" id="bulkDeleteBtn" style="display: none;">
                🗑️ Supprimer sélection
            </button>
        </div>
    </form>

    <!-- Results count -->
    <div class="results-count">
        {{ $conversations->total() }} conversation(s) trouvée(s)
    </div>

    <!-- Table -->
    <div class="card">
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.ai.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Assistant</th>
                            <th>Messages</th>
                            <th>Dernier message</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversations as $conv)
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $conv->id }}" class="conversation-checkbox">
                            </td>
                            <td style="font-family: monospace; font-weight: 600;">
                                #{{ $conv->id }}
                            </td>
                            <td>
                                <span class="badge {{ $conv->assistant }}">
                                    {{ ucfirst($conv->assistant) }}
                                </span>
                            </td>
                            <td style="color: #6b7280;">
                                {{ $conv->messages_count }}
                            </td>
                            <td style="max-width: 400px;">
                                @if($conv->messages->last())
                                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #6b7280;">
                                        {{ Str::limit($conv->messages->last()->content, 80) }}
                                    </div>
                                @else
                                    <span style="color: #9ca3af; font-style: italic;">Aucun message</span>
                                @endif
                            </td>
                            <td style="color: #6b7280;">
                                {{ $conv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.ai.show', $conv) }}" class="btn btn-ghost btn-sm">
                                        Voir
                                    </a>
                                    <form method="POST" action="{{ route('admin.ai.destroy', $conv) }}" onsubmit="return confirm('Êtes-vous sûr ?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: transparent; color: #dc2626;">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 3rem; color: #6b7280;">
                                Aucune conversation trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        {{ $conversations->links() }}
    </div>

</div>

<script>
// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.conversation-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleBulkDelete();
});

// Toggle bulk delete button
document.querySelectorAll('.conversation-checkbox').forEach(cb => {
    cb.addEventListener('change', toggleBulkDelete);
});

function toggleBulkDelete() {
    const checked = document.querySelectorAll('.conversation-checkbox:checked').length;
    document.getElementById('bulkDeleteBtn').style.display = checked > 0 ? 'block' : 'none';
}

function bulkDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer les conversations sélectionnées ?')) {
        document.getElementById('bulkDeleteForm').submit();
    }
}
</script>
@endsection