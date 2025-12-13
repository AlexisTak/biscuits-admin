{{-- resources/views/admin/users/create.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Créer un utilisateur')

@section('content')
<div class="user-create-page">
    <div class="page-header">
        <div>
            <h1 class="page-title">Créer un utilisateur</h1>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">← Annuler</a>
        </div>
    </div>

    <div class="form-card">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Nom complet *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-input">
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-input">
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" required class="form-input">
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">Rôle *</label>
                <select id="role" name="role" required class="form-select">
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Créer l'utilisateur</button>
        </form>
    </div>
</div>

@push('styles')
<style>
.form-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.form-input,
.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.error-text {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--color-red);
}
</style>
@endpush

@endsection