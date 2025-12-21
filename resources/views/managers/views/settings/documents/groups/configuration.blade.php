@extends('layouts.managers')

@section('title', 'Configuración - ' . $group->name)

@section('content')

    @include('managers.includes.card', ['title' => 'Configuración de Grupo: ' . $group->name])

    <div class="widget-content">
        @include('managers.components.alerts')

        <!-- Statistics Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card bg-light-primary stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h6 class="card-title text-primary mb-2">Miembros</h6>
                                <h4 class="mb-1 fw-bold">{{ $group->users->count() }}</h4>
                                <small class="text-muted">Usuarios asignados</small>
                            </div>
                            <i class="fas fa-users text-primary opacity-50" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-success stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h6 class="card-title text-success mb-2">Activo</h6>
                                <h4 class="mb-1 fw-bold">{{ $group->is_active ? 'Sí' : 'No' }}</h4>
                                <small class="text-muted">Estado del grupo</small>
                            </div>
                            <i class="fas fa-check-circle text-success opacity-50" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-info stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h6 class="card-title text-info mb-2">Configuraciones</h6>
                                <h4 class="mb-1 fw-bold">{{ $configurations->sum(fn($cats) => $cats->count()) }}</h4>
                                <small class="text-muted">Opciones disponibles</small>
                            </div>
                            <i class="fas fa-sliders text-info opacity-50" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-warning stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h6 class="card-title text-warning mb-2">Habilitadas</h6>
                                <h4 class="mb-1 fw-bold">
                                    @php
                                        $enabledCount = $configurations->sum(fn($cats) => $cats->where('value', true)->count());
                                    @endphp
                                    {{ $enabledCount }}
                                </h4>
                                <small class="text-muted">Activas</small>
                            </div>
                            <i class="fas fa-toggle-on text-warning opacity-50" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Información del Grupo -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2"></i>Información del Grupo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Nombre</label>
                            <strong>{{ $group->name }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Clave</label>
                            <code class="text-primary small">{{ $group->key }}</code>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Estado</label>
                            <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $group->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Modo de Asignación</label>
                            @php
                                $modeLabels = [
                                    'manual' => 'Manual',
                                    'round_robin' => 'Round Robin',
                                    'load_balanced' => 'Balance de Carga'
                                ];
                            @endphp
                            <small class="d-block">{{ $modeLabels[$group->assignment_mode] ?? $group->assignment_mode }}</small>
                        </div>
                    </div>
                </div>

                <!-- Miembros del Grupo -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-users me-2"></i>Miembros ({{ $group->users->count() }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if($group->users->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($group->users as $user)
                                    <div class="list-group-item px-3 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initials flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($user->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($user->lastname ?? '', 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1 min-width-0">
                                                <small class="d-block text-truncate fw-semibold">
                                                    {{ $user->firstname }} {{ $user->lastname }}
                                                </small>
                                                <small class="text-muted d-block text-truncate">{{ $user->email }}</small>
                                            </div>
                                            @if($user->pivot->priority === 'primary')
                                                <span class="badge bg-primary-subtle text-primary small" data-bs-toggle="tooltip" title="Usuario principal">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary small" data-bs-toggle="tooltip" title="Usuario de respaldo">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 text-center text-muted">
                                <small>Sin miembros asignados</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('manager.settings.documents.groups.edit', $group->id) }}" class="btn btn-sm btn-outline-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i>Editar Grupo
                        </a>
                        <a href="{{ route('manager.settings.documents.groups.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <form method="POST" action="{{ route('manager.settings.documents.groups.update-configuration', $group->id) }}">
                    @csrf

                    @forelse($configurations as $category => $configs)
                        <div class="card mb-3">
                            <!-- Category Header -->
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    @php
                                        $categoryIcons = [
                                            'email_actions' => 'fa fa-envelope',
                                            'workflow' => 'fa fa-stream',
                                            'notifications' => 'fa fa-bell',
                                        ];
                                        $categoryLabels = [
                                            'email_actions' => 'Acciones de Email',
                                            'workflow' => 'Flujo de Trabajo',
                                            'notifications' => 'Notificaciones',
                                        ];
                                    @endphp
                                    <i class="{{ $categoryIcons[$category] ?? 'fas fa-cog' }} me-2"></i>
                                    {{ $categoryLabels[$category] ?? $category }}
                                </h6>
                            </div>

                            <!-- Configurations -->
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($configs as $config)
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="configurations[{{ $config->id }}]"
                                                       id="config_{{ $config->id }}"
                                                       value="1"
                                                       {{ $config->value ? 'checked' : '' }}>
                                                <label class="form-check-label" for="config_{{ $config->id }}">
                                                    <span class="fw-semibold d-block">{{ $config->label }}</span>
                                                    @if($config->description)
                                                        <small class="text-muted d-block">{{ $config->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-circle-info me-2"></i>
                            <strong>Sin configuraciones</strong>
                            <p class="mb-0 small mt-2">Este grupo no tiene configuraciones disponibles aún.</p>
                        </div>
                    @endforelse

                    <!-- Action Buttons -->
                    @if($configurations->isNotEmpty())
                        <div class="card">
                            <div class="card-body d-flex gap-2 justify-content-end">
                                <a href="{{ route('manager.settings.documents.groups.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Guardar Configuración
                                </button>
                            </div>
                        </div>
                    @endif
                </form>

                <!-- Change History Widget -->
                @if($history->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-history me-2"></i>Historial de cambios
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="timeline-list">
                                @foreach($history as $entry)
                                    <div class="timeline-item px-3 py-3 border-bottom">
                                        <div class="d-flex gap-3">
                                            <div class="flex-shrink-0">
                                                <div class="timeline-marker bg-{{ $entry->new_value ? 'success' : 'warning' }}">
                                                    <i class="fas fa-{{ $entry->new_value ? 'toggle-on' : 'toggle-off' }} text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between mb-1">
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $entry->configuration_label }}</h6>
                                                        <small class="text-muted">
                                                            {{ $entry->new_value ? 'Habilitada' : 'Deshabilitada' }}
                                                        </small>
                                                    </div>
                                                    <small class="text-muted text-nowrap ms-2">
                                                        {{ $entry->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <small class="d-block text-muted">
                                                    Por: <strong>{{ $entry->user?->firstname }} {{ $entry->user?->lastname }}</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Sin historial</strong>
                        <p class="mb-0 small mt-2">Aún no se han realizado cambios en las configuraciones de este grupo.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .form-switch .form-check-label {
            margin-left: 0.5rem;
        }

        .form-check-label span {
            font-size: 0.95rem;
        }

        .form-check-label small {
            margin-top: 0.25rem;
        }

        .card-header {
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-list {
            display: flex;
            flex-direction: column;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item:last-child {
            border-bottom: none !important;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .timeline-marker.bg-success {
            background-color: #13C672 !important;
        }

        .timeline-marker.bg-warning {
            background-color: #FEC90F !important;
        }
    </style>

@endsection
