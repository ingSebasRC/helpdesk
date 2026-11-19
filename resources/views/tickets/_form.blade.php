@php
  $ticket = $ticket ?? [];
  $value = function (string $key, string $fallback = '') use ($ticket) {
    return old($key, $ticket[$key] ?? $fallback);
  };
  $technicianValue = old('technician_id', $ticket['technicianId'] ?? '');
@endphp

@if ($errors->any())
  <div class="info-banner" style="margin-bottom: 18px" role="alert">
    <i data-lucide="alert-circle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
@endif

<form action="{{ $action }}" method="POST">
  @csrf
  @if (strtoupper($method) !== 'POST')
    @method($method)
  @endif

  <div class="form-grid">
    <div class="field span-2">
      <label for="ticket-form-title">Título de la solicitud <span class="req">*</span></label>
      <input class="input" id="ticket-form-title" name="title" type="text" value="{{ $value('title') }}" placeholder="Resume el problema en una frase" required />
      @error('title')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field span-2">
      <label for="ticket-form-description">Descripción <span class="req">*</span></label>
      <textarea class="textarea" id="ticket-form-description" name="description" rows="5" placeholder="Describe el problema, cuándo empezó y a quién afecta…" required>{{ $value('description') }}</textarea>
      @error('description')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    @if ($role !== 'usuario')
      <div class="field">
        <label for="ticket-form-user">Solicitante <span class="req">*</span></label>
        <select class="select" id="ticket-form-user" name="user_id" required>
          <option value="">Selecciona un usuario</option>
          @foreach ($users as $user)
            <option value="{{ $user['id'] }}" @selected($value('user_id', $ticket['userId'] ?? '') === $user['id'])>{{ $user['name'] }}</option>
          @endforeach
        </select>
        @error('user_id')<span class="field-error">{{ $message }}</span>@enderror
      </div>
    @endif

    <div class="field">
      <label for="ticket-form-category">Categoría <span class="req">*</span></label>
      <select class="select" id="ticket-form-category" name="category_id" required>
        <option value="">Selecciona una categoría</option>
        @foreach ($categories as $category)
          <option value="{{ $category['id'] }}" @selected($value('category_id', $ticket['categoryId'] ?? '') === $category['id'])>{{ $category['name'] }}</option>
        @endforeach
      </select>
      @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
      <label for="ticket-form-priority">Prioridad</label>
      <select class="select" id="ticket-form-priority" name="priority">
        @foreach ($priorities as $priority)
          <option value="{{ $priority }}" @selected($value('priority', $ticket['priority'] ?? 'Media') === $priority)>{{ $priority }}</option>
        @endforeach
      </select>
      @error('priority')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    @if ($role !== 'usuario')
      <div class="field">
        <label for="ticket-form-status">Estado</label>
        <select class="select" id="ticket-form-status" name="status">
          @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected($value('status', $ticket['status'] ?? 'Abierto') === $status)>{{ $status }}</option>
          @endforeach
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
      </div>
    @endif

    @if ($role === 'admin')
      <div class="field span-2">
        <label for="ticket-form-technician">Técnico asignado</label>
        <select class="select" id="ticket-form-technician" name="technician_id">
          <option value="">Sin asignar</option>
          @foreach ($technicians as $technician)
            <option value="{{ $technician['id'] }}" @selected($technicianValue === $technician['id'])>{{ $technician['name'] }}</option>
          @endforeach
        </select>
        @error('technician_id')<span class="field-error">{{ $message }}</span>@enderror
      </div>
    @endif
  </div>

  <div class="modal-footer" style="padding-left: 0; padding-right: 0">
    <a class="btn btn--secondary" href="{{ route('tickets.index') }}">Cancelar</a>
    <button type="submit" class="btn btn--primary">
      <i data-lucide="check"></i>{{ $submitLabel }}
    </button>
  </div>
</form>
