@php
  $currentUser = auth()->user();
  $roleLabel = match ($currentUser->role) {
    'admin' => 'Administrador',
    'tecnico' => 'Técnico',
    default => 'Usuario',
  };
  $activeTickets = \App\Models\Ticket::whereIn('status', \App\Support\TicketPresenter::ACTIVE_STATUSES);
  if ($currentUser->role === 'usuario') {
    $activeTickets->where('user_id', $currentUser->id);
  }
  $activeCount = $activeTickets->count();
@endphp
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Panel' }} · HelpDesk</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;550;600;650;700&display=swap" rel="stylesheet" />
    @foreach (['variables.css', 'main.css', 'components.css', 'layout.css', 'responsive.css'] as $stylesheet)
      <link rel="stylesheet" href="{{ asset('assets/css/' . $stylesheet) }}" />
    @endforeach
    @stack('styles')
  </head>
  <body data-page="{{ $active ?? 'dashboard' }}">
    <div class="app-shell">
      <aside class="sidebar" id="sidebar" aria-label="Navegación principal">
        <div class="sidebar-brand">
          <a class="brand" href="{{ route('dashboard') }}" aria-label="HelpDesk, ir al panel">
            <span class="brand-mark"><i data-lucide="life-buoy"></i></span>
            <span class="brand-name">HelpDesk</span>
            <span class="brand-badge">Pro</span>
          </a>
          <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Cerrar menú">
            <i data-lucide="x"></i>
          </button>
        </div>

        <nav class="sidebar-nav" aria-label="Navegación principal">
          <span class="nav-label">General</span>
          <a class="nav-item {{ ($active ?? '') === 'dashboard' ? 'is-active' : '' }}" href="{{ route('dashboard') }}" @if(($active ?? '') === 'dashboard') aria-current="page" @endif>
            <i data-lucide="layout-dashboard"></i>
            <span>Panel</span>
          </a>
          <a class="nav-item {{ ($active ?? '') === 'tickets' ? 'is-active' : '' }}" href="{{ route('tickets.index') }}" @if(($active ?? '') === 'tickets') aria-current="page" @endif>
            <i data-lucide="ticket"></i>
            <span>Solicitudes</span>
            <span class="nav-badge">{{ $activeCount }}</span>
          </a>

          @if ($currentUser->role === 'admin')
            <span class="nav-label">Administración</span>
            <a class="nav-item {{ ($active ?? '') === 'categories' ? 'is-active' : '' }}" href="{{ route('categories.index') }}" @if(($active ?? '') === 'categories') aria-current="page" @endif>
              <i data-lucide="folder"></i>
              <span>Categorías</span>
            </a>
            <a class="nav-item" href="{{ route('tickets.index', ['technician' => 'unassigned']) }}">
              <i data-lucide="wrench"></i>
              <span>Sin asignar</span>
            </a>

            <span class="nav-label">Análisis</span>
            <a class="nav-item" href="{{ route('tickets.export') }}">
              <i data-lucide="bar-chart-3"></i>
              <span>Exportar datos</span>
            </a>
          @endif
        </nav>

        <div class="sidebar-footer">
          <div class="sidebar-user">
            <span class="avatar">{{ mb_strtoupper(mb_substr($currentUser->name, 0, 1)) }}</span>
            <div class="sidebar-user-info">
              <p class="sidebar-user-name">{{ $currentUser->name }}</p>
              <p class="sidebar-user-role">{{ $roleLabel }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="sidebar-logout" aria-label="Cerrar sesión" title="Cerrar sesión">
                <i data-lucide="log-out"></i>
              </button>
            </form>
          </div>
        </div>
      </aside>

      <div class="app-overlay" id="app-overlay"></div>

      <div class="app-frame">
        <header class="topbar" id="topbar">
          <button type="button" class="icon-btn mobile-menu" id="menu-toggle" aria-label="Abrir menú">
            <i data-lucide="menu"></i>
          </button>

          <div class="topbar-title">
            <h1 id="topbar-page-title">{{ $title ?? 'Panel' }}</h1>
            <p id="topbar-page-subtitle">{{ $subtitle ?? '' }}</p>
          </div>

          <div class="topbar-actions">
            <form class="topbar-search" action="{{ route('tickets.index') }}" method="GET" role="search">
              <div class="search-field" style="max-width: none; min-width: 0">
                <i data-lucide="search"></i>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar solicitudes…" aria-label="Buscar solicitudes" />
              </div>
            </form>

            <a class="btn btn--primary btn--sm" href="{{ route('tickets.create') }}">
              <i data-lucide="plus"></i><span class="btn-label">Nueva solicitud</span>
            </a>

            <div class="topbar-divider"></div>

            <div class="user-menu">
              <button type="button" class="user-chip" id="user-menu-btn" aria-haspopup="true" aria-expanded="false">
                <span class="avatar">{{ mb_strtoupper(mb_substr($currentUser->name, 0, 1)) }}</span>
                <span class="user-chip-meta">
                  <span class="user-chip-name">{{ $currentUser->name }}</span>
                  <span class="user-chip-role">{{ $roleLabel }}</span>
                </span>
                <i data-lucide="chevron-down"></i>
              </button>
              <div class="dropdown" id="user-dropdown" hidden>
                <div class="dropdown-header">
                  <p class="dropdown-name">{{ $currentUser->name }}</p>
                  <p class="dropdown-mail">{{ $currentUser->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item dropdown-item--danger">
                    <i data-lucide="log-out"></i>Cerrar sesión
                  </button>
                </form>
              </div>
            </div>
          </div>
        </header>

        <main class="page">
          @if (session('status'))
            <div class="info-banner info-banner--accent" role="status">
              <i data-lucide="check-circle"></i>
              <span>{{ session('status') }}</span>
            </div>
          @endif

          @yield('content')
        </main>
      </div>
    </div>

    @yield('modals')

    <div class="toast-stack" id="toast-stack" aria-live="polite"></div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
          window.lucide.createIcons();
        }

        var body = document.body;
        var menuToggle = document.getElementById('menu-toggle');
        var sidebarClose = document.getElementById('sidebar-close');
        var overlay = document.getElementById('app-overlay');
        var userButton = document.getElementById('user-menu-btn');
        var userDropdown = document.getElementById('user-dropdown');

        if (menuToggle) menuToggle.addEventListener('click', function () { body.classList.add('nav-open'); });
        if (sidebarClose) sidebarClose.addEventListener('click', function () { body.classList.remove('nav-open'); });
        if (overlay) overlay.addEventListener('click', function () { body.classList.remove('nav-open'); });

        if (userButton && userDropdown) {
          userButton.addEventListener('click', function (event) {
            event.stopPropagation();
            var isHidden = userDropdown.hidden;
            userDropdown.hidden = !isHidden;
            userButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
          });
          document.addEventListener('click', function (event) {
            if (!event.target.closest('.user-menu')) {
              userDropdown.hidden = true;
              userButton.setAttribute('aria-expanded', 'false');
            }
          });
        }

        window.openHelpdeskModal = function (id) {
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
          }
        };

        window.closeHelpdeskModal = function (id) {
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
          }
        };

        document.querySelectorAll('[data-modal-open]').forEach(function (button) {
          button.addEventListener('click', function () {
            window.openHelpdeskModal(button.getAttribute('data-modal-open'));
          });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function (button) {
          button.addEventListener('click', function () {
            var backdrop = button.closest('.modal-backdrop');
            if (backdrop) window.closeHelpdeskModal(backdrop.id);
          });
        });

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.is-open').forEach(function (modal) {
              window.closeHelpdeskModal(modal.id);
            });
            body.classList.remove('nav-open');
          }
        });
      });
    </script>
    @stack('scripts')
  </body>
</html>
