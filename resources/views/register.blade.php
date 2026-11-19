@extends('layouts.guest')

@section('title', 'Crear cuenta')

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

        <h1 class="login-title">Crear cuenta</h1>
        <p class="login-subtitle">Regístrate para crear y seguir tus solicitudes de soporte.</p>

        <form class="login-form" action="{{ route('register.store') }}" method="POST">
          @csrf

          <div class="field">
            <label for="register-name">Nombre</label>
            <input
              class="input @error('name') is-invalid @enderror"
              id="register-name"
              name="name"
              type="text"
              value="{{ old('name') }}"
              autocomplete="name"
              placeholder="Tu nombre completo"
              spellcheck="false"
              required
              autofocus
            />
            @error('name')
              <span class="field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="field">
            <label for="register-email">Correo electrónico</label>
            <input
              class="input @error('email') is-invalid @enderror"
              id="register-email"
              name="email"
              type="email"
              value="{{ old('email') }}"
              autocomplete="username"
              placeholder="tu@empresa.com"
              spellcheck="false"
              required
            />
            @error('email')
              <span class="field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="field">
            <label for="register-password">Contraseña</label>
            <div class="password-wrap">
              <input
                class="input @error('password') is-invalid @enderror"
                id="register-password"
                name="password"
                type="password"
                autocomplete="new-password"
                placeholder="Introduce tu contraseña"
                required
              />
              <button type="button" class="password-toggle" data-password-toggle="register-password" aria-label="Mostrar contraseña">
                <i data-lucide="eye"></i>
              </button>
            </div>
            @error('password')
              <span class="field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="field">
            <label for="register-password-confirmation">Confirmar contraseña</label>
            <div class="password-wrap">
              <input
                class="input"
                id="register-password-confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="Repite tu contraseña"
                required
              />
              <button type="button" class="password-toggle" data-password-toggle="register-password-confirmation" aria-label="Mostrar contraseña">
                <i data-lucide="eye"></i>
              </button>
            </div>
          </div>

          @if ($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
            <div class="login-error is-visible" role="alert">
              <i data-lucide="alert-circle"></i>
              <span>{{ $errors->first() }}</span>
            </div>
          @endif

          <button class="btn btn--primary btn--block login-submit" type="submit">
            <span>Crear cuenta</span>
            <i data-lucide="arrow-right"></i>
          </button>
        </form>

        <p class="login-subtitle" style="margin-top: 14px; text-align: center">
          ¿Ya tienes cuenta?
          <a class="text-accent" href="{{ route('login') }}"><strong>Inicia sesión</strong></a>
        </p>

        <p class="login-foot">
          Tu cuenta se crea con el rol de usuario y podrás registrar solicitudes de soporte.
        </p>
      </div>
    </main>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var password = document.getElementById(button.getAttribute('data-password-toggle'));
        if (!password) return;

        button.addEventListener('click', function () {
          var isPassword = password.type === 'password';
          password.type = isPassword ? 'text' : 'password';
          button.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
          button.innerHTML = '<i data-lucide="' + (isPassword ? 'eye-off' : 'eye') + '"></i>';
          if (window.lucide) window.lucide.createIcons();
          password.focus();
        });
      });
    });
  </script>
@endpush
