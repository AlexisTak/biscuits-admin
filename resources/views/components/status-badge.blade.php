@props(['status'])

<span class="badge badge-{{ $status }}">
    {{ match($status) {
        'pending' => 'En attente',
        'processed' => 'Traité',
        'archived' => 'Archivé',
        default => ucfirst($status)
    } }}
</span>