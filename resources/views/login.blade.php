@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
  @php
    $heroStats = $heroStats ?? ['tickets' => 0, 'technicians' => 0, 'categories' => 0];
  @endphp

  <div class="login-shell">
    <aside class="login-aside">
      <div class="login-aside-inner">
        <div class="login-brand">
          <span class="brand-mark"><i data-lucide="life-buoy"></i></span>
          <span class="brand-name">HelpDesk</span>
          <span class="brand-badge">Pro</span>
        </div>

        <div class="login-hero">
          <h1>Soporte técnico,<br /><span>sin fricción.</span></h1>
          <p>
            Centraliza las solicitudes de tu organización, asigna al técnico adecuado
            y mide el rendimiento del servicio desde un único lugar.
          </p>
        </div>

        <div class="login-features">
          <div class="login-feature">
            <span class="login-feature-icon"><i data-lucide="ticket"></i></span>
            <div>
              <p class="login-feature-title">Solicitudes centralizadas</p>
              <p class="login-feature-text">Un único flujo para registrar, priorizar y resolver incidencias.</p>
            </div>
          </div>
          <div class="login-feature">
            <span class="login-feature-icon"><i data-lucide="activity"></i></span>
            <div>
              <p class="login-feature-title">Seguimiento en tiempo real</p>
              <p class="login-feature-text">Historial, comentarios y cambios de estado siempre visibles.</p>
            </div>
          </div>
          <div class="login-feature">
            <span class="login-feature-icon"><i data-lucide="bar-chart-3"></i></span>
            <div>
              <p class="login-feature-title">Métricas que importan</p>
              <p class="login-feature-text">Reportes claros sobre carga de trabajo y tiempos de resolución.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="login-aside-footer">
        <div>
          <p class="login-stat-value">{{ $heroStats['tickets'] }}</p>
          <p class="login-stat-label">Solicitudes gestionadas</p>
        </div>
        <div>
          <p class="login-stat-value">{{ $heroStats['technicians'] }}</p>
          <p class="login-stat-label">Técnicos en el equipo</p>
        </div>
        <div>
          <p class="login-stat-value">{{ $heroStats['categories'] }}</p>
          <p class="login-stat-label">Categorías de soporte</p>
        </div>
      </div>
    </aside>

    <main class="login-main">
      <div class="login-panel">
        <div class="login-panel-head">
          <span class="brand-mark"><i data-lucide="life-buoy"></i></span>
          <span class="brand-name">HelpDesk</span>
        </div>

        <h1 class="login-title">Inicia sesión</h1>
        <p class="login-subtitle">Accede con tu cuenta corporativa para gestionar el soporte.</p>

        <form class="login-form" action="{{ route('login.store') }}" method="POST">
          @csrf

          <div class="field">
            <label for="login-email">Correo corporativo</label>
            <input
              class="input @error('email') is-invalid @enderror"
              id="login-email"
              name="email"
              type="email"
              value="{{ old('email') }}"
              autocomplete="username"
              placeholder="tu@empresa.com"
              spellcheck="false"
              required
              autofocus
            />
            @error('email')
              <span class="field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="field">
            <label for="login-password">Contraseña</label>
            <div class="password-wrap">
              <input
                class="input @error('password') is-invalid @enderror"
                id="login-password"
                name="password"
                type="password"
                autocomplete="current-password"
                placeholder="Introduce tu contraseña"
                required
              />
              <button type="button" class="password-toggle" id="toggle-password" aria-label="Mostrar contraseña">
                <i data-lucide="eye"></i>
              </button>
            </div>
            @error('password')
              <span class="field-error">{{ $message }}</span>
            @enderror
          </div>

          @if ($errors->has('credentials') || ($errors->any() && !$errors->has('email') && !$errors->has('password')))
            <div class="login-error is-visible" role="alert">
              <i data-lucide="alert-circle"></i>
              <span>{{ $errors->first() }}</span>
            </div>
          @endif

          <button class="btn btn--primary btn--block login-submit" type="submit">
            <span>Entrar al panel</span>
            <i data-lucide="arrow-right"></i>
          </button>
        </form>

        <p class="login-subtitle" style="margin-top: 14px; text-align: center">
          ¿No tienes cuenta?
          <a class="text-accent" href="{{ route('register') }}"><strong>Regístrate aquí</strong></a>
        </p>

        <div class="demo-card">
          <p class="demo-title"><i data-lucide="sparkles"></i>Acceso Rapido de demostración</p>
          <div class="demo-accounts">
            <button
              type="button"
              class="demo-account"
              onclick="document.getElementById('login-email').value='admin@helpdesk.com';document.getElementById('login-password').value='admin123';"
            >
              <span class="demo-account-icon"><i data-lucide="shield-check"></i></span>
              <span class="demo-account-text">
                <span class="demo-account-name">Administrador</span>
                <span class="demo-account-mail">admin@helpdesk.com / admin123</span>
              </span>
              <i data-lucide="arrow-up-right"></i>
            </button>
            <button
              type="button"
              class="demo-account"
              onclick="document.getElementById('login-email').value='tecnico@helpdesk.com';document.getElementById('login-password').value='tecnico123';"
            >
              <span class="demo-account-icon"><i data-lucide="wrench"></i></span>
              <span class="demo-account-text">
                <span class="demo-account-name">Técnico de soporte</span>
                <span class="demo-account-mail">tecnico@helpdesk.com / tecnico123</span>
              </span>
              <i data-lucide="arrow-up-right"></i>
            </button>
          </div>
        </div>

        <p class="login-foot">
          Las solicitudes se gestionan en el servidor con peticiones HTTP.<br />
          Los cambios quedan disponibles para todo el equipo.
        </p>
      </div>
    </main>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var toggle = document.getElementById('toggle-password');
      var password = document.getElementById('login-password');

      if (!toggle || !password) return;

      toggle.addEventListener('click', function () {
        var isPassword = password.type === 'password';
        password.type = isPassword ? 'text' : 'password';
        toggle.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
        toggle.innerHTML = '<i data-lucide="' + (isPassword ? 'eye-off' : 'eye') + '"></i>';
        if (window.lucide) window.lucide.createIcons();
        password.focus();
      });
    });
  </script>
@endpush
