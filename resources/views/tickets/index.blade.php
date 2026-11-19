@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
  @php
    $sortUrl = function (string $key) use ($filters) {
      $direction = $filters['sort'] === $key && $filters['direction'] === 'asc' ? 'desc' : 'asc';
      return route('tickets.index', array_filter(array_merge($filters, ['sort' => $key, 'direction' => $direction])));
    };
  @endphp

  <div class="page-head">
    <div>
      <h2>Solicitudes</h2>
      <p>Administra el ciclo completo de las solicitudes de soporte.</p>
    </div>
    <div class="page-head-actions">
      @if ($role === 'admin')
        <a class="btn btn--secondary" href="{{ route('tickets.export', array_filter($filters)) }}">
          <i data-lucide="download"></i>Exportar CSV
        </a>
      @endif
      <button type="button" class="btn btn--primary" data-modal-open="ticket-modal">
        <i data-lucide="plus"></i>Nueva solicitud
      </button>
    </div>
  </div>

  <div class="card table-card">
    <form class="toolbar" action="{{ route('tickets.index') }}" method="GET">
      <input type="hidden" name="sort" value="{{ $filters['sort'] }}" />
      <input type="hidden" name="direction" value="{{ $filters['direction'] }}" />

      <div class="search-field">
        <i data-lucide="search"></i>
        <input
          class="input"
          type="search"
          name="q"
          value="{{ $filters['q'] }}"
          placeholder="Buscar por título, código, solicitante o técnico…"
          aria-label="Buscar solicitudes"
        />
      </div>

      <div class="filters">
        <label class="sr-only" for="filter-status">Estado</label>
        <select class="select" id="filter-status" name="status">
          <option value="">Todos los estados</option>
          @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
          @endforeach
        </select>

        <label class="sr-only" for="filter-priority">Prioridad</label>
        <select class="select" id="filter-priority" name="priority">
          <option value="">Todas las prioridades</option>
          @foreach ($priorities as $priority)
            <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ $priority }}</option>
          @endforeach
        </select>

        <label class="sr-only" for="filter-category">Categoría</label>
        <select class="select" id="filter-category" name="category">
          <option value="">Todas las categorías</option>
          @foreach ($categories as $category)
            <option value="{{ $category['id'] }}" @selected($filters['category'] === $category['id'])>{{ $category['name'] }}</option>
          @endforeach
        </select>

        <label class="sr-only" for="filter-technician">Técnico</label>
        <select class="select" id="filter-technician" name="technician">
          <option value="">Todos los técnicos</option>
          @if ($role === 'admin')
            <option value="unassigned" @selected($filters['technician'] === 'unassigned')>Sin asignar</option>
          @endif
          @foreach ($technicians as $technician)
            <option value="{{ $technician['id'] }}" @selected($filters['technician'] === $technician['id'])>{{ $technician['name'] }}</option>
          @endforeach
        </select>

        <button type="submit" class="btn btn--ghost btn--sm"><i data-lucide="filter"></i>Filtrar</button>
        <a class="btn btn--ghost btn--sm" href="{{ route('tickets.index') }}"><i data-lucide="x"></i>Limpiar</a>
      </div>
    </form>

    <div class="table-wrap">
      <table class="data-table" id="tickets-table">
        <thead>
          <tr>
            @foreach (['code' => 'Código', 'title' => 'Solicitud', 'user' => 'Solicitante', 'technician' => 'Técnico', 'priority' => 'Prioridad', 'status' => 'Estado', 'updatedAt' => 'Actualizado'] as $key => $label)
              <th class="sortable {{ $filters['sort'] === $key ? 'is-sorted' : '' }}" scope="col">
                <a href="{{ $sortUrl($key) }}">
                  {{ $label }}
                  <span class="sort-indicator">
                    <i data-lucide="{{ $filters['sort'] !== $key ? 'chevrons-up-down' : ($filters['direction'] === 'asc' ? 'arrow-up' : 'arrow-down') }}"></i>
                  </span>
                </a>
              </th>
            @endforeach
            <th class="th-actions" scope="col">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($tickets as $ticket)
            <tr class="is-clickable" onclick="window.location.href='{{ route('tickets.show', $ticket['id']) }}'">
              <td><span class="code-chip">{{ $ticket['code'] }}</span></td>
              <td>
                <span class="cell-main" title="{{ $ticket['title'] }}">{{ $ticket['title'] }}</span>
                <span class="cell-sub">
                  <span class="dot" style="background: {{ \App\Support\HelpdeskData::categoryColor($ticket['categoryId']) }}"></span>
                  {{ \App\Support\HelpdeskData::categoryName($ticket['categoryId']) }}
                </span>
              </td>
              <td>
                <div class="cell-user">
                  @include('partials.avatar', ['name' => \App\Support\HelpdeskData::userName($ticket['userId']), 'class' => 'avatar--sm'])
                  <div class="cell-user-text">
                    <span class="cell-user-name">{{ \App\Support\HelpdeskData::userName($ticket['userId']) }}</span>
                    <span class="cell-user-sub">{{ \App\Support\HelpdeskData::userDepartment($ticket['userId']) }}</span>
                  </div>
                </div>
              </td>
              <td>
                @if ($ticket['technicianId'])
                  <div class="cell-user">
                    @include('partials.avatar', ['name' => \App\Support\HelpdeskData::technicianName($ticket['technicianId']), 'class' => 'avatar--sm'])
                    <div class="cell-user-text">
                      <span class="cell-user-name">{{ \App\Support\HelpdeskData::technicianName($ticket['technicianId']) }}</span>
                      <span class="cell-user-sub">Técnico</span>
                    </div>
                  </div>
                @else
                  <span class="text-muted">Sin asignar</span>
                @endif
              </td>
              <td>@include('partials.priority-badge', ['priority' => $ticket['priority']])</td>
              <td>@include('partials.status-badge', ['status' => $ticket['status']])</td>
              <td class="text-muted nowrap" title="{{ \Carbon\Carbon::parse($ticket['updatedAt'])->locale('es')->format('d/m/Y H:i') }}">
                {{ \Carbon\Carbon::parse($ticket['updatedAt'])->locale('es')->diffForHumans() }}
              </td>
              <td class="td-actions">
                <div class="row-actions" onclick="event.stopPropagation()">
                  <a class="icon-btn" href="{{ route('tickets.show', $ticket['id']) }}" title="Ver detalle" aria-label="Ver detalle">
                    <i data-lucide="eye"></i>
                  </a>
                  @if ($role === 'admin' || ($role === 'usuario' && $ticket['userId'] === (string) auth()->id()))
                    <a class="icon-btn" href="{{ route('tickets.edit', $ticket['id']) }}" title="Editar" aria-label="Editar">
                      <i data-lucide="pencil"></i>
                    </a>
                  @endif
                  @if ($role === 'admin')
                    <form action="{{ route('tickets.destroy', $ticket['id']) }}" method="POST" onsubmit="return confirm('¿Eliminar la solicitud {{ $ticket['code'] }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="icon-btn icon-btn--danger" title="Eliminar" aria-label="Eliminar">
                        <i data-lucide="trash-2"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <span class="empty-state-icon"><i data-lucide="search"></i></span>
                  <p class="empty-state-title">No hay solicitudes</p>
                  <p class="empty-state-text">Ajusta los filtros o crea una nueva solicitud para empezar.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer">
      <span>
        Mostrando {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }} de {{ $tickets->total() }} solicitudes
      </span>
      <div class="pagination-controls">
        @if ($tickets->onFirstPage())
          <span class="btn btn--ghost btn--sm is-disabled">Anterior</span>
        @else
          <a class="btn btn--ghost btn--sm" href="{{ $tickets->previousPageUrl() }}">Anterior</a>
        @endif

        <span>Página {{ $tickets->currentPage() }} de {{ $tickets->lastPage() }}</span>

        @if ($tickets->hasMorePages())
          <a class="btn btn--ghost btn--sm" href="{{ $tickets->nextPageUrl() }}">Siguiente</a>
        @else
          <span class="btn btn--ghost btn--sm is-disabled">Siguiente</span>
        @endif
      </div>
    </div>
  </div>
@endsection

@section('modals')
  <div class="modal-backdrop" id="ticket-modal" aria-hidden="true">
    <div class="modal modal--lg" role="dialog" aria-modal="true" aria-labelledby="ticket-modal-title">
      <div class="modal-header">
        <div>
          <h3 class="modal-title" id="ticket-modal-title">Nueva solicitud</h3>
          <p class="modal-subtitle">Registra una nueva incidencia o petición de soporte.</p>
        </div>
        <button type="button" class="icon-btn" data-modal-close aria-label="Cerrar">
          <i data-lucide="x"></i>
        </button>
      </div>

      @if ($errors->any())
        <div class="info-banner" style="margin: 16px 20px 0" role="alert">
          <i data-lucide="alert-circle"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form class="modal-body" action="{{ route('tickets.store') }}" method="POST">
        @csrf
        <div class="form-grid">
          <div class="field span-2">
            <label for="ticket-title">Título de la solicitud <span class="req">*</span></label>
            <input class="input" id="ticket-title" name="title" type="text" value="{{ old('title') }}" placeholder="Resume el problema en una frase" required />
            @error('title')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field span-2">
            <label for="ticket-description">Descripción <span class="req">*</span></label>
            <textarea class="textarea" id="ticket-description" name="description" rows="4" placeholder="Describe el problema, cuándo empezó y a quién afecta…" required>{{ old('description') }}</textarea>
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          @if ($role !== 'usuario')
            <div class="field">
              <label for="ticket-user">Solicitante <span class="req">*</span></label>
              <select class="select" id="ticket-user" name="user_id" required>
                <option value="">Selecciona un usuario</option>
                @foreach ($users as $user)
                  <option value="{{ $user['id'] }}" @selected(old('user_id') === $user['id'])>{{ $user['name'] }}</option>
                @endforeach
              </select>
              @error('user_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>
          @endif

          <div class="field">
            <label for="ticket-category">Categoría <span class="req">*</span></label>
            <select class="select" id="ticket-category" name="category_id" required>
              <option value="">Selecciona una categoría</option>
              @foreach ($categories as $category)
                <option value="{{ $category['id'] }}" @selected(old('category_id') === $category['id'])>{{ $category['name'] }}</option>
              @endforeach
            </select>
            @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label for="ticket-priority">Prioridad</label>
            <select class="select" id="ticket-priority" name="priority">
              @foreach ($priorities as $priority)
                <option value="{{ $priority }}" @selected(old('priority', 'Media') === $priority)>{{ $priority }}</option>
              @endforeach
            </select>
            @error('priority')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          @if ($role !== 'usuario')
            <div class="field">
              <label for="ticket-status">Estado</label>
              <select class="select" id="ticket-status" name="status">
                @foreach ($statuses as $status)
                  <option value="{{ $status }}" @selected(old('status', 'Abierto') === $status)>{{ $status }}</option>
                @endforeach
              </select>
              @error('status')<span class="field-error">{{ $message }}</span>@enderror
            </div>
          @endif

          @if ($role === 'admin')
            <div class="field">
              <label for="ticket-technician">Técnico asignado</label>
              <select class="select" id="ticket-technician" name="technician_id">
                <option value="">Sin asignar</option>
                @foreach ($technicians as $technician)
                  <option value="{{ $technician['id'] }}" @selected(old('technician_id') === $technician['id'])>{{ $technician['name'] }}</option>
                @endforeach
              </select>
              @error('technician_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>
          @endif

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" data-modal-close>Cancelar</button>
          <button type="submit" class="btn btn--primary">
            <i data-lucide="check"></i>Crear solicitud
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  @if ($showCreate || $errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        window.openHelpdeskModal('ticket-modal');
      });
    </script>
  @endif
@endpush
