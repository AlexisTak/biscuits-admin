@extends('admin.layouts.app')

@section('title', 'AI Chat - Statistiques')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/ai.css') }}">
@endpush

@section('content')
<div class="ai-container">
    
    <!-- Header -->
    <div class="ai-header-actions">
        <div>
            <h1 class="mb-2">📊 Statistiques avancées</h1>
            <p style="color: #6b7280; margin: 0;">Analyse détaillée des conversations AI</p>
        </div>
        <a href="{{ route('admin.ai.index') }}" class="btn btn-secondary">
            ← Retour
        </a>
    </div>

    <!-- Stats par Assistant -->
    <div class="card mb-8">
        <div class="card-header">
            <h2>Performance par Assistant</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($statsByAssistant as $stat)
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; text-transform: capitalize;">
                            {{ $stat->assistant }}
                        </h3>
                        <span style="font-size: 2rem;">
                            @if($stat->assistant === 'support') 💬
                            @elseif($stat->assistant === 'dev') 💻
                            @elseif($stat->assistant === 'sales') 💰
                            @endif
                        </span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0 0 0.25rem 0;">Conversations</p>
                            <p style="font-size: 1.75rem; font-weight: bold; color: #1f2937; margin: 0;">
                                {{ number_format($stat->conversations) }}
                            </p>
                        </div>
                        <div>
                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0 0 0.25rem 0;">Messages totaux</p>
                            <p style="font-size: 1.25rem; font-weight: 600; color: #4b5563; margin: 0;">
                                {{ number_format($stat->messages) }}
                            </p>
                        </div>
                        <div>
                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0 0 0.25rem 0;">Moy. messages/conv</p>
                            <p style="font-size: 1.125rem; font-weight: 500; color: #2563eb; margin: 0;">
                                {{ $stat->conversations > 0 ? round($stat->messages / $stat->conversations, 1) : 0 }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Graphique Conversations -->
    <div class="card mb-8">
        <div class="card-header">
            <h2>Conversations - 30 derniers jours</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="conversationsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Graphique Messages par heure -->
    <div class="card mb-8">
        <div class="card-header">
            <h2>Activité par heure (7 derniers jours)</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Utilisateurs -->
    @if($topUsers->count() > 0)
    <div class="card">
        <div class="card-header">
            <h2>Top 10 Utilisateurs</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Conversations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topUsers as $index => $user)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            {{ $user->user?->name ?? 'Utilisateur #' . $user->user_id }}
                        </td>
                        <td style="color: #6b7280;">
                            {{ $user->user?->email ?? '-' }}
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: #2563eb; background: #dbeafe; border-radius: 9999px;">
                                {{ $user->conversations }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Conversations Chart
const conversationsData = @json($conversationsPerDay);
const ctx1 = document.getElementById('conversationsChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: conversationsData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
        }),
        datasets: [{
            label: 'Conversations',
            data: conversationsData.map(d => d.count),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Hourly Chart
const hourlyData = @json($messagesByHour);
const ctx2 = document.getElementById('hourlyChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: Array.from({length: 24}, (_, i) => i + 'h'),
        datasets: [{
            label: 'Messages',
            data: Array.from({length: 24}, (_, i) => {
                const found = hourlyData.find(d => d.hour === i);
                return found ? found.count : 0;
            }),
            backgroundColor: 'rgba(168, 85, 247, 0.8)',
            borderColor: 'rgb(168, 85, 247)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
@endsection