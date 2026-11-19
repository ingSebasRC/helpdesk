@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
  <div class="page-head">
    <div>
      <h2>Usuarios</h2>
      <p>Consulta los usuarios registrados y asigna sus roles de acceso.</p>
    </div>
  </div>

  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table" id="users-table">
        <thead>
          <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Correo</th>
            <th scope="col">Rol actual</th>
            <th class="th-actions" scope="col">Cambiar rol</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $account)
            @php
              $roleLabel = match ($account->role) {
                'admin' => 'Administrador',
                'tecnico' => 'Técnico',
                default => 'Usuario',
              };
              $roleTone = match ($account->role) {
                'admin' => 'accent',
                'tecnico' => 'info',
                default => 'neutral',
              };
            @endphp
            <tr>
              <td>
                <div class="cell-user">
                  @include('partials.avatar', ['name' => $account->name, 'class' => 'avatar--sm'])
                  <div class="cell-user-text">
                    <span class="cell-user-name">{{ $account->name }}</span>
                    @if ($account->is(auth()->user()))
                      <span class="cell-user-sub">Tu cuenta</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="text-muted">{{ $account->email }}</td>
              <td><span class="badge badge--{{ $roleTone }}">{{ $roleLabel }}</span></td>
              <td class="td-actions">
                @if ($account->is(auth()->user()))
                  <span class="text-muted">No puedes cambiar tu propio rol</span>
                @else
                  <form action="{{ route('users.update', $account) }}" method="POST" style="display: flex; gap: 8px; justify-content: flex-end">
                    @csrf
                    @method('PUT')
                    <label class="sr-only" for="role-{{ $account->id }}">Rol de {{ $account->name }}</label>
                    <select class="select select--sm" id="role-{{ $account->id }}" name="role">
                      @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected($account->role === $role)>
                          {{ match ($role) { 'admin' => 'Administrador', 'tecnico' => 'Técnico', default => 'Usuario' } }}
                        </option>
                      @endforeach
                    </select>
                    <button type="submit" class="btn btn--primary btn--sm"><i data-lucide="save"></i>Guardar</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4">
                <div class="empty-state">
                  <span class="empty-state-icon"><i data-lucide="users"></i></span>
                  <p class="empty-state-title">No hay usuarios registrados</p>
                  <p class="empty-state-text">Los usuarios aparecerán aquí una vez que se registren en el sistema.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
