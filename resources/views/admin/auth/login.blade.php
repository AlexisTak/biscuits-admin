{{-- resources/views/admin/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow"> {{-- Empêche l'indexation par Google --}}
    <title>Connexion Admin - Biscuits Dev</title>
    
    <style>
        /* ... (Ton CSS de base est conservé, j'ai ajouté ceci pour le toggle password) ... */
        :root { --primary: #3b82f6; --bg: #0a0a0a; --card-bg: #141414; --text: #f5f5f5; --error: #ef4444; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        
        .login-container { width: 100%; max-width: 400px; } /* Légèrement réduit pour le focus */
        .login-card { background: var(--card-bg); border: 1px solid #262626; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5); }
        
        .logo { width: 56px; height: 56px; margin: 0 auto 1.5rem; background: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; }
        .logo svg { width: 32px; height: 32px; }
        
        h1 { text-align: center; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .subtitle { text-align: center; font-size: 0.875rem; color: #737373; margin-bottom: 2rem; }
        
        .form-group { margin-bottom: 1.25rem; position: relative; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: #d4d4d4; margin-bottom: 0.5rem; }
        
        .input-wrapper { position: relative; }
        input[type="email"], input[type="text"], input[type="password"] {
            width: 100%; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #262626;
            border-radius: 0.5rem; color: #f5f5f5; font-size: 0.9375rem; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); }
        input.error { border-color: var(--error); }
        
        /* Bouton Show/Hide Password */
        .toggle-password {
            position: absolute; right: 0; top: 0; height: 100%; padding: 0 1rem;
            background: transparent; border: none; color: #737373; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .toggle-password:hover { color: #d4d4d4; }
        
        .error-msg { display: flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; font-size: 0.8rem; color: var(--error); }
        .submit-btn {
            width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none;
            border-radius: 0.5rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background 0.2s; margin-top: 1rem;
        }
        .submit-btn:hover { background: #2563eb; }
        .submit-btn:disabled { opacity: 0.7; cursor: not-allowed; }
        
        .btn-loader { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; display: none; }
        .loading .btn-loader { display: block; }
        .loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .remember-me { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #a3a3a3; }
        .footer-text { text-align: center; margin-top: 2rem; font-size: 0.75rem; color: #525252; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/></svg>
            </div>
            <h1>Administration</h1>
            <p class="subtitle">Veuillez vous authentifier pour continuer</p>

            <form method="POST" action="{{ route('admin.login.submit') }}" id="loginForm" autocomplete="off">
                @csrf
                
                {{-- Email --}}
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <input 
                        type="email" id="email" name="email" value="{{ old('email') }}"
                        required autofocus autocomplete="email"
                        autocapitalize="off" spellcheck="false"
                        class="{{ $errors->has('email') ? 'error' : '' }}"
                    >
                    @error('email')
                        <div class="error-msg">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" id="password" name="password"
                            required autocomplete="current-password"
                            class="{{ $errors->has('password') ? 'error' : '' }}"
                        >
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" onclick="togglePassword()">
                            {{-- Icon Eye Open --}}
                            <svg class="eye-open" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">Connexion</span>
                    <span class="btn-loader"></span>
                </button>
            </form>
        </div>
        <p class="footer-text">IP: {{ request()->ip() }} &bull; © {{ date('Y') }} Biscuits Dev</p>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                btn.style.color = '#f5f5f5'; // Highlight when visible
            } else {
                input.type = 'password';
                btn.style.color = '';
            }
        }

        // Loading State
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if(document.getElementById('email').value && document.getElementById('password').value) {
                btn.disabled = true;
                btn.classList.add('loading');
            }
        });

        // Anti-Clickjacking
        if (window.self !== window.top) { window.top.location.href = window.location.href; }
    </script>
</body>
</html>