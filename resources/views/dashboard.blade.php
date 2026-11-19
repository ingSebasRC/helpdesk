@extends('layouts.app')

@section('title', 'Panel general')

@section('content')
  @php $userRole = $role ?? auth()->user()->role; @endphp

  <div class="page-head">
    <div>
      <h2>Panel general</h2>
      <p>Estado del servicio de soporte y actividad del equipo.</p>
    </div>
    <div class="page-head-actions">
      @if ($userRole === 'admin')
        <a class="btn btn--secondary" href="{{ route('tickets.export') }}">
          <i data-lucide="bar-chart-3"></i>Ver reportes
        </a>
      @endif
      <a class="btn btn--primary" href="{{ route('tickets.create') }}">
        <i data-lucide="plus"></i>Nueva solicitud
      </a>
    </div>
  </div>

  <section class="stats-grid" aria-label="Indicadores principales">
    @foreach ($statCards as $card)
      <article class="stat-card">
        <div class="stat-top">
          <span class="stat-icon stat-icon--{{ $card['tone'] }}"><i data-lucide="{{ $card['icon'] }}"></i></span>
          @if ($card['badge'])
            <span class="trend trend--{{ $card['badge']['tone'] }}">
              <i data-lucide="{{ $card['badge']['icon'] }}"></i>{{ $card['badge']['text'] }}
            </span>
          @endif
        </div>
        <div>
          <p class="stat-value">{{ $card['value'] }}</p>
          <p class="stat-label">{{ $card['label'] }}</p>
        </div>
        <p class="stat-foot">{{ $card['foot'] }}</p>
      </article>
    @endforeach
  </section>

  <section class="card" style="margin-top: 18px" aria-label="Contadores por estado">
    <div class="card-header">
      <div>
        <h3 class="card-title">Contadores por estado</h3>
        <p class="card-subtitle">Total de solicitudes en cada fase del flujo</p>
      </div>
      @if ($userRole !== 'usuario')
        <a class="btn btn--ghost btn--sm" href="{{ route('tickets.index') }}">
          Ver solicitudes<i data-lucide="arrow-right"></i>
        </a>
      @endif
    </div>
    <div class="card-body">
      <div style="display: flex; flex-wrap: wrap; gap: 10px">
        @foreach ($stats['byStatus'] as $status => $count)
          <span class="btn btn--ghost btn--sm" style="pointer-events: none; gap: 8px">
            @include('partials.status-badge', ['status' => $status])
            <strong>{{ $count }}</strong>
          </span>
        @endforeach
      </div>
    </div>
  </section>

  @if ($userRole !== 'usuario')
    <div class="grid-2 grid-2--uneven">
      <div class="card">
        <div class="card-header">
          <div>
            <h3 class="card-title">Tendencia de solicitudes</h3>
          <p class="card-subtitle">Solicitudes creadas y resueltas por día</p>
        </div>
        <div class="card-header-actions">
          <form action="{{ route('dashboard') }}" method="GET">
            <label class="sr-only" for="trend-range">Periodo de la tendencia</label>
            <select class="select select--sm" id="trend-range" name="range" onchange="this.form.submit()">
              @foreach ([7, 14, 30] as $option)
                <option value="{{ $option }}" @selected($days === $option)>{{ $option }} días</option>
              @endforeach
            </select>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-box">
          <canvas id="chart-trend" role="img" aria-label="Tendencia de solicitudes creadas y resueltas"></canvas>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Distribución por estado</h3>
          <p class="card-subtitle">Situación actual de las solicitudes</p>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-box chart-box--sm">
          <canvas id="chart-status" role="img" aria-label="Distribución de solicitudes por estado"></canvas>
        </div>
      </div>
    </div>
    </div>
  @endif

  @if ($userRole !== 'usuario')
  <div class="{{ $userRole === 'admin' ? 'grid-2 grid-2--uneven' : '' }}">
    <div class="card card--flush">
      <div class="card-header">
        <div>
          <h3 class="card-title">Solicitudes recientes</h3>
          <p class="card-subtitle">Últimas actualizaciones del servicio</p>
        </div>
        <a class="btn btn--ghost btn--sm" href="{{ route('tickets.index') }}">
          Ver todas<i data-lucide="arrow-right"></i>
        </a>
      </div>
      <div class="table-wrap">
        <table class="data-table data-table--compact">
          <thead>
            <tr>
              <th>Código</th>
              <th>Solicitud</th>
              <th>Estado</th>
              <th>Actualizado</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recent as $ticket)
              <tr class="is-clickable" onclick="window.location.href='{{ route('tickets.show', $ticket['id']) }}'">
                <td><span class="code-chip">{{ $ticket['code'] }}</span></td>
                <td>
                  <span class="cell-main">{{ $ticket['title'] }}</span>
                  <span class="cell-sub">
                    <span class="dot" style="background: {{ \App\Support\HelpdeskData::categoryColor($ticket['categoryId']) }}"></span>
                    {{ \App\Support\HelpdeskData::categoryName($ticket['categoryId']) }}
                  </span>
                </td>
                <td>@include('partials.status-badge', ['status' => $ticket['status']])</td>
                <td class="text-muted nowrap">{{ \Carbon\Carbon::parse($ticket['updatedAt'])->locale('es')->diffForHumans() }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4">
                  <div class="empty-state">
                    <span class="empty-state-icon"><i data-lucide="inbox"></i></span>
                    <p class="empty-state-title">Sin solicitudes todavía</p>
                    <p class="empty-state-text">Crea la primera solicitud para empezar a dar seguimiento.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if ($userRole === 'admin')
      @php
        $maxActive = max(1, ...array_column($stats['technicians'], 'active'));
      @endphp

    <div class="card card--flush">
      <div class="card-header">
        <div>
          <h3 class="card-title">Carga del equipo</h3>
          <p class="card-subtitle">Solicitudes activas por técnico</p>
        </div>
        <a class="btn btn--ghost btn--sm" href="{{ route('tickets.index', ['technician' => 'unassigned']) }}">
          Ver equipo<i data-lucide="arrow-right"></i>
        </a>
      </div>
      <div class="workload-list">
        @foreach ($stats['technicians'] as $technician)
          <a class="workload-item" href="{{ route('tickets.index', ['technician' => $technician['id']]) }}">
            @include('partials.avatar', ['name' => $technician['name']])
            <div class="workload-info">
              <div class="workload-head">
                <span class="workload-name">{{ $technician['name'] }}</span>
                <span class="workload-count">{{ $technician['active'] }} activa{{ $technician['active'] === 1 ? '' : 's' }}</span>
              </div>
              <div class="progress">
                <span class="progress-bar" style="width: {{ round($technician['active'] / $maxActive * 100) }}%; background: {{ $technician['status'] === 'Inactivo' ? '#c4c9d2' : '#5b5bd6' }}"></span>
              </div>
              <p class="workload-meta">{{ $technician['specialty'] }} · {{ $technician['resolved'] }} resueltas</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>
    @endif
  </div>
  @endif
@endsection

@push('scripts')
  @if (auth()->user()->role !== 'usuario')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!window.Chart) return;

      var trend = @json($trend);
      new Chart(document.getElementById('chart-trend'), {
        type: 'line',
        data: {
          labels: trend.labels,
          datasets: [
            {
              label: 'Creadas',
              data: trend.created,
              borderColor: '#5b5bd6',
              backgroundColor: 'rgba(91, 91, 214, 0.08)',
              fill: true,
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 0,
              pointHoverRadius: 4
            },
            {
              label: 'Resueltas',
              data: trend.resolved,
              borderColor: '#178f63',
              backgroundColor: 'rgba(23, 143, 99, 0.06)',
              fill: true,
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 0,
              pointHoverRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle' } }
          },
          scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 7 } },
            y: { beginAtZero: true, grid: { color: '#eef0f3' }, border: { display: false }, ticks: { precision: 0, maxTicksLimit: 5 } }
          }
        }
      });

      var statusColors = {
        'Abierto': '#5b5bd6',
        'En progreso': '#2f80ed',
        'En espera': '#d99a06',
        'Resuelto': '#1a9c6b',
        'Cerrado': '#8b919c'
      };
      var byStatus = @json(array_filter($stats['byStatus']));
      var labels = Object.keys(byStatus).filter(function (key) { return byStatus[key] > 0; });
      var canvas = document.getElementById('chart-status');

      if (!labels.length) {
        canvas.style.display = 'none';
        return;
      }

      new Chart(canvas, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: labels.map(function (label) { return byStatus[label]; }),
            backgroundColor: labels.map(function (label) { return statusColors[label] || '#8b919c'; }),
            borderColor: '#ffffff',
            borderWidth: 2,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 14 } }
          }
        }
      });
    });
  </script>
  @endif
@endpush
