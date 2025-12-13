@props(['contact'])

<div class="actions-card">
    <h3 class="card-title">Actions</h3>

    {{-- Modifier --}}
    <a href="{{ route('admin.contacts.edit', $contact) }}" class="action-btn action-btn-primary">
        ✏️ Modifier le contact
    </a>

    {{-- Changer le statut --}}
    <div class="action-group">
        <h4>Statut</h4>
        <form action="{{ route('admin.contacts.update-status', $contact) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <select name="status" class="status-select" onchange="this.form.submit()">
                <option value="pending" @selected($contact->status === 'pending')>En attente</option>
                <option value="processed" @selected($contact->status === 'processed')>Traité</option>
                <option value="archived" @selected($contact->status === 'archived')>Archivé</option>
            </select>
        </form>
    </div>

    {{-- Répondre --}}
    <div class="action-group">
        <h4>Répondre</h4>
        <a href="mailto:{{ $contact->email }}?subject=Re: {{ urlencode($contact->service ?? 'Contact') }}" 
           class="action-btn action-btn-primary">
            <x-icon name="mail" />
            Envoyer un email
        </a>
        
        @if($contact->phone)
        <a href="tel:{{ $contact->phone }}" class="action-btn action-btn-primary" style="margin-top: 10px;">
            <x-icon name="phone" />
            Appeler ({{ $contact->phone }})
        </a>
        @endif
    </div>

    {{-- Ajouter une note --}}
    <div class="action-group">
        <h4>Ajouter une note</h4>
        <form action="{{ route('admin.contacts.add-note', $contact) }}" method="POST">
            @csrf
            <textarea name="note" rows="3" placeholder="Note interne..." class="note-textarea" required></textarea>
            <button type="submit" class="action-btn action-btn-secondary">
                <x-icon name="file-text" />
                Ajouter
            </button>
        </form>
    </div>

    {{-- Actions rapides --}}
    <div class="action-group">
        <h4>Actions rapides</h4>
        
        @if($contact->status === 'pending')
        <form action="{{ route('admin.contacts.update-status', $contact) }}" method="POST" style="margin-bottom: 0.5rem;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="processed">
            <button type="submit" class="action-btn action-btn-success">
                ✓ Marquer comme traité
            </button>
        </form>
        @endif

        <form action="{{ route('admin.contacts.update-status', $contact) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="archived">
            <button type="submit" class="action-btn action-btn-warning">
                📦 Archiver
            </button>
        </form>
    </div>

    {{-- Zone de danger --}}
    <div class="danger-zone">
        <h4>Zone de danger</h4>
        <form action="{{ route('admin.contacts.destroy', $contact) }}" 
              method="POST" 
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-btn action-btn-danger">
                🗑️ Supprimer définitivement
            </button>
        </form>
    </div>
</div>