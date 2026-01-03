@extends('layouts.theme')

@section('title', 'Administración de módulos')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <form id="moduleForm" enctype="multipart/form-data" role="form" onSubmit="return false">
                @csrf

                {{-- Success/Error Messages --}}
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Modules Management Section --}}
                <div class="card-body">
                    <div class="d-flex no-block align-items-center justify-content-between mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-cube me-2"></i>Administración de módulos
                        </h5>
                        <a href="{{ route('settings.modules.uploadForm') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Instalar módulo
                        </a>
                    </div>

                    <p class="card-subtitle mb-4">
                        Administra todos los módulos del sistema. Desde aquí puedes habilitar, deshabilitar,
                        instalar o desinstalar módulos. Los módulos protegidos (Role y Modules) no pueden ser
                        modificados para evitar inestabilidad del sistema.
                    </p>

                    {{-- Statistics Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary-subtle border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fas fa-cubes fa-lg"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold">{{ $totalModules }}</h3>
                                            <small class="text-muted">Total de módulos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success-subtle border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fas fa-check-circle fa-lg"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold text-success">{{ $enabledCount }}</h3>
                                            <small class="text-muted">Habilitados</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning-subtle border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fas fa-pause-circle fa-lg"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold text-warning">{{ $disabledCount }}</h3>
                                            <small class="text-muted">Deshabilitados</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modules Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-top">
                            <thead>
                                <tr class="border-bottom">
                                    <th class="fw-semibold text-muted ps-3">Módulo</th>
                                    <th class="fw-semibold text-muted">Descripción</th>
                                    <th class="fw-semibold text-muted">Versión</th>
                                    <th class="fw-semibold text-muted">Estado</th>
                                    <th class="fw-semibold text-muted text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($modules as $module)
                                    <tr class="border-bottom">
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded bg-light p-2 me-3">
                                                    <i class="fas fa-cube fa-lg text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $module['name'] }}</div>
                                                    <small class="text-muted">{{ $module['alias'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ Str::limit($module['description'] ?: 'Sin descripción', 80) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">v{{ $module['version'] }}</span>
                                        </td>
                                        <td>
                                            @if($module['enabled'])
                                                <span class="badge bg-success-subtle text-success border border-success">
                                                    <i class="fas fa-circle fa-2xs me-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                                    <i class="fas fa-circle fa-2xs me-1"></i>Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                {{-- View Details --}}
                                                <a href="{{ route('settings.modules.show', $module['alias']) }}"
                                                   class="btn btn-sm btn-light border" title="Ver detalles"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Enable/Disable/Uninstall --}}
                                                @if(!in_array($module['name'], ['Role', 'Modules']))
                                                    @if($module['enabled'])
                                                        <form action="{{ route('settings.modules.disable', $module['alias']) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning-subtle border border-warning"
                                                                    title="Deshabilitar" data-bs-toggle="tooltip"
                                                                    onclick="return confirm('¿Deshabilitar {{ $module['name'] }}?')">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('settings.modules.enable', $module['alias']) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success-subtle border border-success"
                                                                    title="Habilitar" data-bs-toggle="tooltip">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Uninstall --}}
                                                    <form action="{{ route('settings.modules.uninstall', $module['alias']) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger-subtle border border-danger"
                                                                title="Desinstalar" data-bs-toggle="tooltip"
                                                                onclick="return confirm('¿Desinstalar {{ $module['name'] }}? Esta acción es irreversible.')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-light text-muted border">
                                                        <i class="fas fa-lock fa-xs me-1"></i>Protegido
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox me-2"></i>No hay módulos disponibles
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    });
</script>
@endpush
