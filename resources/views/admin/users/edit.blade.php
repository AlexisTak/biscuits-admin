{{-- resources/views/admin/users/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Modifier ' . $user->name)

@section('content')
<div class="user-edit-page">
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier {{ $user->name }}</h1>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">← Annuler</a>
        </div>
    </div>

    <div class="form-card">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nom complet *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="password" name="password" class="form-input">
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">Rôle *</label>
                <select id="role" name="role" required class="form-select">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
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