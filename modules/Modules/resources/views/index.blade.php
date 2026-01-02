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
                        <a href="{{ route('modules.uploadForm') }}" class="btn btn-primary btn-sm">
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
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $totalModules }}</h3>
                                    <small class="text-muted">Total de módulos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light border-start border-success border-4">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-success">{{ $enabledCount }}</h3>
                                    <small class="text-muted">Módulos habilitados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light border-start border-danger border-4">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-danger">{{ $disabledCount }}</h3>
                                    <small class="text-muted">Módulos deshabilitados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modules Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-top">Módulo</th>
                                    <th class="border-top">Descripción</th>
                                    <th class="border-top">Versión</th>
                                    <th class="border-top">Estado</th>
                                    <th class="border-top text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($modules as $module)
                                    <tr>
                                        <td>
                                            <strong>{{ $module['name'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $module['alias'] }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $module['description'] ?: 'Sin descripción' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $module['version'] }}</span>
                                        </td>
                                        <td>
                                            @if($module['enabled'])
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                {{-- View Details --}}
                                                <a href="{{ route('modules.show', $module['alias']) }}"
                                                   class="btn btn-outline-info" title="Ver detalles"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Enable/Disable/Uninstall --}}
                                                @if(!in_array($module['name'], ['Role', 'Modules']))
                                                    @if($module['enabled'])
                                                        <form action="{{ route('modules.disable', $module['alias']) }}"
                                                              method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-warning"
                                                                    title="Deshabilitar" data-bs-toggle="tooltip"
                                                                    onclick="return confirm('¿Desabilitar {{ $module['name'] }}?')">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('modules.enable', $module['alias']) }}"
                                                              method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success"
                                                                    title="Habilitar" data-bs-toggle="tooltip">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Uninstall --}}
                                                    <form action="{{ route('modules.uninstall', $module['alias']) }}"
                                                          method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger"
                                                                title="Desinstalar" data-bs-toggle="tooltip"
                                                                onclick="return confirm('¿Desinstalar {{ $module['name'] }}? Esta acción es irreversible.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-light text-muted">Protegido</span>
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
