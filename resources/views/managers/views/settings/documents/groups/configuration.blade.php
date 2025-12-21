@extends('layouts.managers')

@section('title', 'Configuración - ' . $group->name)

@section('content')

    @include('managers.includes.card', ['title' => 'Configuración de Grupo: ' . $group->name])

    <div class="widget-content">
        @include('managers.components.alerts')

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="fas fa-info-circle me-2"></i>Información del Grupo
                        </h6>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Nombre</label>
                            <strong>{{ $group->name }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Clave</label>
                            <code class="text-primary">{{ $group->key }}</code>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Estado</label>
                            <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $group->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block mb-1">Miembros</label>
                            <span class="badge bg-primary">{{ $group->users->count() }}</span>
                        </div>
                        <div>
                            <label class="small text-muted d-block mb-1">Modo de Asignación</label>
                            @php
                                $modeLabels = [
                                    'manual' => 'Manual',
                                    'round_robin' => 'Round Robin',
                                    'load_balanced' => 'Balance de Carga'
                                ];
                            @endphp
                            <small>{{ $modeLabels[$group->assignment_mode] ?? $group->assignment_mode }}</small>
                        </div>
                    </div>
                </div>

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
    </style>

@endsection
