{{-- resources/views/admin/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') - Biscuits Dev</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                <img src="../icon.svg" alt="icon" width="64px">
                <span>Biscuits Dev</span>
            </a>
            <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <line x1="3" y1="12" x2="21" y2="12" stroke-width="2"/>
                    <line x1="3" y1="6" x2="21" y2="6" stroke-width="2"/>
                    <line x1="3" y1="18" x2="21" y2="18" stroke-width="2"/>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">
                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7" stroke-width="2"/>
                            <rect x="14" y="3" width="7" height="7" stroke-width="2"/>
                            <rect x="14" y="14" width="7" height="7" stroke-width="2"/>
                            <rect x="3" y="14" width="7" height="7" stroke-width="2"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- Contacts --}}
                <li class="nav-item">
                    <a href="{{ route('admin.contacts.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                            <circle cx="9" cy="7" r="4" stroke-width="2"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="2"/>
                        </svg>
                        <span>Contacts</span>
                        @if($pendingContacts ?? 0)
                        <span class="badge-count">{{ $pendingContacts }}</span>
                        @endif
                    </a>
                </li>

                {{-- Devis --}}
                <li class="nav-item">
                    <a href="{{ route('admin.devis.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.devis.*') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                            <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                            <line x1="16" y1="13" x2="8" y2="13" stroke-width="2"/>
                            <line x1="16" y1="17" x2="8" y2="17" stroke-width="2"/>
                        </svg>
                        <span>Devis</span>
                        @if($pendingDevis ?? 0)
                        <span class="badge-count">{{ $pendingDevis }}</span>
                        @endif
                    </a>
                </li>

                {{-- Utilisateurs --}}
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2"/>
                            <circle cx="9" cy="7" r="4" stroke-width="2"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke-width="2"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-width="2"/>
                        </svg>
                        <span>Utilisateurs</span>
                    </a>
                </li>

                <li class="nav-item">
                <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>
                    <span>Tickets</span>
                    @if(\App\Models\Ticket::where('status', 'open')->count() > 0)
                    <span class="badge">
                        {{ \App\Models\Ticket::where('status', 'open')->count() }}
                    </span>
                    @endif
                </a>
            </li>
                            
                <li class="nav-item">
                    <a href="{{ route('admin.ai.index') }}"
                       class="nav-link {{ request()->routeIs('admin.ai') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3h6M5 8v6a5 5 0 0 0 5 5h4a5 5 0 0 0 5-5V8zM9 12h.01M15 12h.01"/>
                        </svg>
                    <span>AI Assistance</span>
                    </a>
                </li>
                {{-- Divider --}}
                <li class="nav-divider"></li>

                {{-- Activity Logs --}}
                <li class="nav-item">
                    <a href="{{ route('admin.activity-logs') }}" 
                       class="nav-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                            <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                        </svg>
                        <span>Logs</span>
                    </a>
                </li>

                {{-- Settings --}}
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            <path d="M12 1v6m0 6v6M5.6 5.6l4.2 4.2m4.2 4.2l4.2 4.2m-12-8.4l4.2-4.2m4.2 4.2l4.2-4.2" stroke-width="2"/>
                        </svg>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </nav>

        {{-- User Menu --}}
        <div class="sidebar-footer">
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="user-details">
                        <p class="user-name">{{ auth()->user()->name }}</p>
                        <p class="user-role">Administrateur</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout" title="Déconnexion">
                        <svg width="26" height="26" fill="none" stroke="currentColor">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-width="2"/>
                            <polyline points="16 17 21 12 16 7" stroke-width="2"/>
                            <line x1="21" y1="12" x2="9" y2="12" stroke-width="2"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="admin-main" id="main-content">
        {{-- Top Bar --}}
        <header class="admin-topbar">
            <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <line x1="3" y1="12" x2="21" y2="12" stroke-width="2"/>
                    <line x1="3" y1="6" x2="21" y2="6" stroke-width="2"/>
                    <line x1="3" y1="18" x2="21" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <div class="topbar-search">
                <svg width="26" height="26" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8" stroke-width="2"/>
                    <path d="m21 21-4.35-4.35" stroke-width="2"/>
                </svg>
                <input type="search" placeholder="Rechercher..." class="search-input">
            </div>

        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success" role="alert">
            <svg class="alert-icon" width="26" height="26" fill="none" stroke="currentColor">
                <path d="M20 6L9 17l-5-5" stroke-width="2"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error" role="alert">
            <svg class="alert-icon" width="26" height="26" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path d="M12 8v4m0 4h.01" stroke-width="2"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error" role="alert">
            <svg class="alert-icon" width="26" height="26" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path d="M12 8v4m0 4h.01" stroke-width="2"/>
            </svg>
            <div>
                <strong>Erreurs de validation:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Page Content --}}
        <div class="admin-content">
            @yield('content')
        </div>
    </main>

    {{-- Scripts --}}
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')

    <script>
    // Toggle sidebar sur mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }

    // Fermer sidebar au clic dehors (mobile)
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        if (window.innerWidth < 768 && 
            sidebar.classList.contains('open') && 
            !sidebar.contains(e.target) && 
            !e.target.closest('.mobile-menu-toggle')) {
            sidebar.classList.remove('open');
        }
    });

    // Auto-hide flash messages
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    </script>
</body>
</html>