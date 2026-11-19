@extends('layouts.app')

@section('title', 'Detalle de solicitud')

@section('content')
  <div class="detail-head">
    <a class="detail-back" href="{{ route('tickets.index') }}">
      <i data-lucide="arrow-left"></i>Volver a solicitudes
    </a>

    @if ($ticket)
      <div class="detail-title-row">
        <div>
          <div class="detail-code">
            <span class="code-chip">{{ $ticket['code'] }}</span>
            @include('partials.status-badge', ['status' => $ticket['status']])
            @include('partials.priority-badge', ['priority' => $ticket['priority']])
          </div>
          <h2 class="detail-title">{{ $ticket['title'] }}</h2>
          <div class="detail-meta">
            <span class="detail-meta-item"><i data-lucide="calendar"></i>Creada {{ \Carbon\Carbon::parse($ticket['createdAt'])->locale('es')->format('d/m/Y H:i') }}</span>
            <span class="detail-meta-item"><i data-lucide="clock"></i>Actualizada {{ \Carbon\Carbon::parse($ticket['updatedAt'])->locale('es')->diffForHumans() }}</span>
          </div>
        </div>
        <div class="detail-actions">
          @if ($role === 'admin' || ($role === 'usuario' && $ticket['userId'] === (string) auth()->id()))
            <button type="button" class="btn btn--secondary btn--sm" data-modal-open="detail-edit-modal">
              <i data-lucide="pencil"></i>Editar
            </button>
          @endif
          @if ($role === 'admin')
            <form action="{{ route('tickets.destroy', $ticket['id']) }}" method="POST" onsubmit="return confirm('¿Eliminar la solicitud {{ $ticket['code'] }}?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn--danger btn--sm"><i data-lucide="trash-2"></i>Eliminar</button>
            </form>
          @endif
        </div>
      </div>
    @endif
  </div>

  @if (!$ticket)
    <div class="card" id="detail-empty">
      <div class="empty-state">
        <span class="empty-state-icon"><i data-lucide="file-question"></i></span>
        <p class="empty-state-title">Solicitud no encontrada</p>
        <p class="empty-state-text">
          El enlace no es válido o la solicitud fue eliminada. Vuelve al listado para
          consultar las solicitudes disponibles.
        </p>
        <a class="btn btn--primary" href="{{ route('tickets.index') }}" style="margin-top: 8px">
          <i data-lucide="arrow-left"></i>Ir a solicitudes
        </a>
      </div>
    </div>
  @else
    <div class="detail-layout">
      <div class="detail-main">
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Descripción</h3>
              <p class="card-subtitle">Detalle aportado por el solicitante</p>
            </div>
          </div>
          <div class="card-body">
            <p class="prose">{{ $ticket['description'] }}</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Historial de actividad</h3>
              <p class="card-subtitle">Todos los cambios registrados en la solicitud</p>
            </div>
          </div>
          <div class="timeline">
            @foreach (array_reverse($ticket['history']) as $event)
              <div class="timeline-item">
                <div class="timeline-rail">
                  <span class="timeline-dot"><i data-lucide="history"></i></span>
                </div>
                <div class="timeline-content">
                  <p class="timeline-title">{{ $event['action'] }}</p>
                  <p class="timeline-detail">{{ $event['detail'] }}</p>
                  <p class="timeline-time">{{ $event['author'] }} · {{ \Carbon\Carbon::parse($event['createdAt'])->locale('es')->format('d/m/Y H:i') }}</p>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Notas de seguimiento</h3>
              <p class="card-subtitle">Comunicación interna y notificaciones del equipo</p>
            </div>
          </div>
          <div class="card-body">
            @forelse ($ticket['notes'] ?? [] as $note)
              <div class="timeline-item">
                <div class="timeline-rail">
                  <span class="timeline-dot"><i data-lucide="message-square"></i></span>
                </div>
                <div class="timeline-content">
                  <p class="timeline-title">
                    {{ $note['author'] ?? 'Equipo de soporte' }}
                    @if (! empty($note['notify']))
                      <span class="badge badge--info">Notificación enviada</span>
                    @endif
                  </p>
                  <p class="timeline-detail">{{ $note['message'] ?? '' }}</p>
                  <p class="timeline-time">@isset($note['createdAt']){{ \Carbon\Carbon::parse($note['createdAt'])->locale('es')->format('d/m/Y H:i') }}@endisset</p>
                </div>
              </div>
            @empty
              <p class="text-muted">Sin notas de seguimiento todavía.</p>
            @endforelse

            @if ($role === 'admin' || $role === 'tecnico')
              <form action="{{ route('tickets.note', $ticket['id']) }}" method="POST" style="margin-top: 18px">
                @csrf
                <div class="field">
                  <label for="note-message">Agregar nota de seguimiento</label>
                  <textarea class="textarea" id="note-message" name="message" rows="3" placeholder="Describe la actuación o el avance de la solicitud…" required>{{ old('message') }}</textarea>
                  @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; margin: 10px 0">
                  <input type="checkbox" name="notify" value="1" @checked(old('notify')) />
                  Notificar al solicitante
                </label>
                <button type="submit" class="btn btn--primary btn--sm"><i data-lucide="send"></i>Guardar nota</button>
              </form>
            @endif
          </div>
        </div>
      </div>

      <aside class="detail-side">
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Detalles</h3>
              <p class="card-subtitle">Información de la solicitud</p>
            </div>
          </div>
          <div class="detail-props">
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="hash"></i>Código</span><span class="detail-prop-value">{{ $ticket['code'] }}</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="activity"></i>Estado</span><span class="detail-prop-value">@include('partials.status-badge', ['status' => $ticket['status']])</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="flag"></i>Prioridad</span><span class="detail-prop-value">@include('partials.priority-badge', ['priority' => $ticket['priority']])</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="tag"></i>Categoría</span><span class="detail-prop-value">{{ \App\Support\HelpdeskData::categoryName($ticket['categoryId']) }}</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="user"></i>Solicitante</span><span class="detail-prop-value">{{ \App\Support\HelpdeskData::userName($ticket['userId']) }}</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="wrench"></i>Técnico</span><span class="detail-prop-value">{{ \App\Support\HelpdeskData::technicianName($ticket['technicianId']) }}</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="calendar"></i>Creada</span><span class="detail-prop-value">{{ \Carbon\Carbon::parse($ticket['createdAt'])->locale('es')->format('d/m/Y') }}</span></div>
            <div class="detail-prop"><span class="detail-prop-label"><i data-lucide="alarm-clock"></i>Fecha límite</span><span class="detail-prop-value {{ $ticket['dueDate'] && \Carbon\Carbon::parse($ticket['dueDate'])->isPast() && !in_array($ticket['status'], ['Resuelto', 'Cerrado'], true) ? 'detail-overdue' : '' }}">{{ $ticket['dueDate'] ? \Carbon\Carbon::parse($ticket['dueDate'])->locale('es')->format('d/m/Y') : '—' }}</span></div>
          </div>
        </div>

        @if ($role !== 'usuario')
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Acciones rápidas</h3>
              <p class="card-subtitle">Actualiza el seguimiento al instante</p>
            </div>
          </div>
          <form action="{{ route('tickets.update', $ticket['id']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="quick-actions">
              <div class="field">
                <label for="quick-status">Estado de la solicitud</label>
                <select class="select" id="quick-status" name="status">
                  @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $ticket['status']) === $status)>{{ $status }}</option>
                  @endforeach
                </select>
              </div>
              @if ($role === 'admin')
                <div class="field">
                  <label for="quick-technician">Técnico asignado</label>
                  <select class="select" id="quick-technician" name="technician_id">
                    <option value="">Sin asignar</option>
                    @foreach ($technicians as $technician)
                      <option value="{{ $technician['id'] }}" @selected(old('technician_id', $ticket['technicianId'] ?? '') === $technician['id'])>{{ $technician['name'] }}</option>
                    @endforeach
                  </select>
                </div>
              @endif
              <button type="submit" class="btn btn--primary btn--sm btn--block"><i data-lucide="save"></i>Guardar cambios</button>
              <p class="field-hint">Los cambios se guardan al enviar el formulario.</p>
            </div>
          </form>
        </div>
        @endif
      </aside>
    </div>
  @endif
@endsection

@section('modals')
  @if ($ticket && ($role === 'admin' || ($role === 'usuario' && $ticket['userId'] === (string) auth()->id())))
    <div class="modal-backdrop" id="detail-edit-modal" aria-hidden="true">
      <div class="modal modal--lg" role="dialog" aria-modal="true" aria-labelledby="detail-edit-title">
        <div class="modal-header">
          <div>
            <h3 class="modal-title" id="detail-edit-title">Editar solicitud</h3>
            <p class="modal-subtitle">Actualiza la información de {{ $ticket['code'] }}.</p>
          </div>
          <button type="button" class="icon-btn" data-modal-close aria-label="Cerrar">
            <i data-lucide="x"></i>
          </button>
        </div>

        @if ($errors->any() && !$errors->has('message'))
          <div class="info-banner" style="margin: 16px 20px 0" role="alert">
            <i data-lucide="alert-circle"></i>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form class="modal-body" action="{{ route('tickets.update', $ticket['id']) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-grid">
            <div class="field span-2">
              <label for="edit-title">Título de la solicitud <span class="req">*</span></label>
              <input class="input" id="edit-title" name="title" type="text" value="{{ old('title', $ticket['title']) }}" required />
              @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field span-2">
              <label for="edit-description">Descripción <span class="req">*</span></label>
              <textarea class="textarea" id="edit-description" name="description" rows="4" required>{{ old('description', $ticket['description']) }}</textarea>
              @error('description')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @if ($role === 'admin')
              <div class="field">
                <label for="edit-user">Solicitante <span class="req">*</span></label>
                <select class="select" id="edit-user" name="user_id" required>
                  @foreach ($users as $user)
                    <option value="{{ $user['id'] }}" @selected(old('user_id', $ticket['userId']) === $user['id'])>{{ $user['name'] }}</option>
                  @endforeach
                </select>
                @error('user_id')<span class="field-error">{{ $message }}</span>@enderror
              </div>
            @endif
            <div class="field">
              <label for="edit-category">Categoría <span class="req">*</span></label>
              <select class="select" id="edit-category" name="category_id" required>
                @foreach ($categories as $category)
                  <option value="{{ $category['id'] }}" @selected(old('category_id', $ticket['categoryId']) === $category['id'])>{{ $category['name'] }}</option>
                @endforeach
              </select>
              @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
              <label for="edit-priority">Prioridad</label>
              <select class="select" id="edit-priority" name="priority">
                @foreach ($priorities as $priority)
                  <option value="{{ $priority }}" @selected(old('priority', $ticket['priority']) === $priority)>{{ $priority }}</option>
                @endforeach
              </select>
              @error('priority')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            @if ($role === 'admin')
              <div class="field">
                <label for="edit-status">Estado</label>
                <select class="select" id="edit-status" name="status">
                  @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $ticket['status']) === $status)>{{ $status }}</option>
                  @endforeach
                </select>
                @error('status')<span class="field-error">{{ $message }}</span>@enderror
              </div>
              <div class="field">
                <label for="edit-technician">Técnico asignado</label>
                <select class="select" id="edit-technician" name="technician_id">
                  <option value="">Sin asignar</option>
                  @foreach ($technicians as $technician)
                    <option value="{{ $technician['id'] }}" @selected(old('technician_id', $ticket['technicianId'] ?? '') === $technician['id'])>{{ $technician['name'] }}</option>
                  @endforeach
                </select>
                @error('technician_id')<span class="field-error">{{ $message }}</span>@enderror
              </div>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn--primary"><i data-lucide="check"></i>Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.location.hash === '#editar') {
        window.openHelpdeskModal('detail-edit-modal');
      }
      @if ($errors->any() && !$errors->has('message'))
        window.openHelpdeskModal('detail-edit-modal');
      @endif
    });
  </script>
@endpush
