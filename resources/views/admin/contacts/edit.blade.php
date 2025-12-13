@extends('admin.layouts.app')

@section('title', 'Modifier le Contact : ' . $contact->name)

@section('content')
<div class="contact-edit">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier le contact</h1>
            <p class="page-subtitle">{{ $contact->name }} (ID: {{ $contact->id }})</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.contacts.show', $contact) }}" class="btn-secondary">← Voir le contact</a>
        </div>
    </div>

    {{-- Formulaire de Modification --}}
    <form action="{{ route('admin.contacts.update', $contact) }}" method="POST" class="form-card">
        @csrf
        {{-- Utilisation de la méthode PUT ou PATCH pour les mises à jour --}}
        @method('PUT') 

        <div class="form-grid">
            
            <div class="form-group span-full">
                <h3 class="form-section-title">Informations Personnelles</h3>
            </div>

            {{-- Nom --}}
            <div class="form-group">
                <label for="name">Nom complet *</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $contact->name) }}" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $contact->email) }}" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Numéro de Téléphone --}}
            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $contact->phone) }}">
                @error('phone')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Pays --}}
            <div class="form-group">
                <label for="country">Pays *</label>
                <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $contact->country) }}" required>
                @error('country')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group span-full">
                <h3 class="form-section-title">Détails de la demande</h3>
            </div>
            
            {{-- Service --}}
            <div class="form-group">
                <label for="service">Service souhaité</label>
                {{-- Supposons ici que 'service' est un champ texte ou que vous utilisez des options définies --}}
                <input type="text" id="service" name="service" class="form-control @error('service') is-invalid @enderror" value="{{ old('service', $contact->service) }}">
                @error('service')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Statut --}}
            <div class="form-group">
                <label for="status">Statut *</label>
                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status', $contact->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="processed" {{ old('status', $contact->status) == 'processed' ? 'selected' : '' }}>Traité</option>
                    <option value="archived" {{ old('status', $contact->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Message (Pleine largeur) --}}
            <div class="form-group span-full">
                <label for="message">Message du contact *</label>
                <textarea id="message" name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message', $contact->message) }}</textarea>
                @error('message')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group span-full">
                <h3 class="form-section-title">Adresse (Optionnel)</h3>
            </div>
            
            {{-- Adresse --}}
            <div class="form-group">
                <label for="address">Adresse</label>
                <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $contact->address) }}">
                @error('address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Code Postal --}}
            <div class="form-group">
                <label for="zip_code">Code Postal</label>
                <input type="text" id="zip_code" name="zip_code" class="form-control @error('zip_code') is-invalid @enderror" value="{{ old('zip_code', $contact->zip_code) }}">
                @error('zip_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>


            {{-- Notes (Pleine largeur) --}}
            <div class="form-group span-full">
                <label for="notes">Notes internes (Historique)</label>
                <textarea id="notes" name="notes" rows="8" class="form-control notes-field @error('notes') is-invalid @enderror">{{ old('notes', $contact->notes) }}</textarea>
                @error('notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Utilisez le panneau latéral de la vue 'show' pour ajouter des notes avec horodatage, ou modifiez l'historique directement ici.</small>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('admin.contacts.show', $contact) }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Styles de base pour un formulaire d'administration */
.form-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.span-full {
    grid-column: 1 / -1;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.form-control {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.2s;
}

.form-control:focus {
    border-color: var(--accent-primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.1);
}

.is-invalid {
    border-color: var(--color-red);
}

.invalid-feedback {
    color: var(--color-red);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 1rem;
}

.btn-primary {
    background: var(--accent-primary);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: var(--accent-hover);
}

.notes-field {
    font-family: monospace; /* Pour une meilleure lisibilité des logs */
}
</style>
@endpush

@endsection