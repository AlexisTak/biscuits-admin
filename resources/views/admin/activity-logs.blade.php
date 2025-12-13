{{-- resources/views/admin/activity-logs.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Logs d\'activité')

@section('content')
<div class="activity-logs-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Logs d'activité</h1>
            <p class="page-subtitle">Historique complet des actions</p>
        </div>
    </div>

    {{-- Table des logs --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Détails</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs['data'] as $log)
                    <tr>
                        <td class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="font-medium">{{ $log->user->name ?? 'Système' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                        <td>
                            <span class="badge badge-{{ $log->type }}">
                                {{ ucfirst($log->type) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                            Aucun log d'activité
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin-pagination :paginator="$contacts" />
    </div>
</div>

@endsection