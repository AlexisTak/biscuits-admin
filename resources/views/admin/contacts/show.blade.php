@extends('admin.layouts.app')

@section('title', 'Contact - ' . $contact->name)

@section('content')
<div class="contact-show">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $contact->name }}</h1>
            <p class="page-subtitle">
                {{ $contact->email }}
                @if($contact->phone) • {{ $contact->phone }} @endif
                • {{ $contact->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.contacts.index') }}" class="btn-secondary">
                ← Retour
            </a>
        </div>
    </div>

    <div class="contact-grid">
        {{-- Informations principales --}}
        <div class="info-card">
            <div class="card-header">
                <h2 class="card-title">Informations</h2>
                <x-status-badge :status="$contact->status" />
            </div>

            <div class="info-grid">
                <x-contact-info-item
                    icon="user"
                    label="Nom complet"
                    :value="$contact->name"
                />

                <x-contact-info-item
                    icon="mail"
                    label="Email"
                    :value="$contact->email"
                    :href="'mailto:' . $contact->email"
                />

                <x-contact-info-item
                    icon="phone"
                    label="Téléphone"
                    :value="$contact->phone ?? 'Non fourni'"
                    :href="$contact->phone ? 'tel:' . $contact->phone : null"
                />

                <x-contact-info-item
                    icon="briefcase"
                    label="Service souhaité"
                    :value="$contact->service ?? 'Non spécifié'"
                />

                <x-contact-info-item
                    icon="map-pin"
                    label="Adresse"
                    :value="$contact->address ?? 'Non fourni'"
                />

                <x-contact-info-item
                    icon="hash"
                    label="Code Postal"
                    :value="$contact->zip_code ?? 'Non fourni'"
                />

                <x-contact-info-item
                    icon="globe"
                    label="Pays"
                    :value="$contact->country"
                />

                {{-- RETIRÉ : Compteur de devis (à réactiver plus tard) --}}
                {{-- 
                <x-contact-info-item
                    icon="file-text"
                    label="Nombre de devis"
                >
                    <span class="info-value">
                        {{ $contact->devis_count ?? 0 }} devis
                        @if($contact->devis_count > 0)
                            <a href="{{ route('admin.devis.index', ['search' => $contact->email]) }}" 
                               class="stat-link">
                                → Voir
                            </a>
                        @endif
                    </span>
                </x-contact-info-item>
                --}}

                <x-contact-info-item
                    icon="clock"
                    label="Date de contact"
                    :value="$contact->created_at->format('d/m/Y à H:i')"
                />
            </div>

            {{-- Message --}}
            <div class="message-section">
                <h3>Message</h3>
                <div class="message-content">{{ $contact->message }}</div>
            </div>

            {{-- Notes internes --}}
            @if($contact->notes)
            <div class="notes-section">
                <h3>Notes internes</h3>
                <div class="notes-content">{{ $contact->notes }}</div>
            </div>
            @endif
        </div>

        {{-- Actions rapides --}}
        <x-contact-actions-sidebar :contact="$contact" />
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/contacts.css') }}">
@endpush