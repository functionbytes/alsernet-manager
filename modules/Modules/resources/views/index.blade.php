@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cube me-2"></i>Administración de módulos
        </h1>
        <a href="{{ route('modules.uploadForm') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Instalar módulo
        </a>
    </div>

    {{-- Alert Messages --}}
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

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total de módulos</div>
                    <div class="h3 mb-0">{{ $totalModules }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Módulos habilitados</div>
                    <div class="h3 mb-0">{{ $enabledCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Módulos deshabilitados</div>
                    <div class="h3 mb-0">{{ $disabledCount }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modules Table --}}
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de módulos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Versión</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
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
                                    <small>{{ Str::limit($module['description'], 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $module['version'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $module['priority'] }}</span>
                                </td>
                                <td>
                                    @if($module['enabled'])
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Habilitado
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Deshabilitado
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        {{-- View Details --}}
                                        <a href="{{ route('modules.show', $module['alias']) }}"
                                           class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Enable/Disable --}}
                                        @if(!in_array($module['name'], ['Role', 'Modules']))
                                            @if($module['enabled'])
                                                <form action="{{ route('modules.disable', $module['alias']) }}"
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning"
                                                            title="Deshabilitar"
                                                            onclick="return confirm('¿Desabilitar el módulo {{ $module['name'] }}?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('modules.enable', $module['alias']) }}"
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                            title="Habilitar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Uninstall --}}
                                            <form action="{{ route('modules.uninstall', $module['alias']) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        title="Desinstalar"
                                                        onclick="return confirm('¿Desinstalar el módulo {{ $module['name'] }}? Esta acción no se puede deshacer.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Módulo protegido</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox me-2"></i>No hay módulos disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Enabled Modules Section --}}
    @if(!empty($enabledModules))
    <div class="card mt-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-check-circle me-2"></i>Módulos habilitados ({{ count($enabledModules) }})
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($enabledModules as $module)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 border-success">
                        <div class="card-body">
                            <h5 class="card-title">{{ $module['name'] }}</h5>
                            <p class="card-text small text-muted">{{ Str::limit($module['description'], 60) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">v{{ $module['version'] }}</span>
                                <a href="{{ route('modules.show', $module['alias']) }}" class="btn btn-sm btn-outline-primary">
                                    Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #90bb13;
    }
    .border-left-success {
        border-left: 4px solid #13C672;
    }
    .border-left-warning {
        border-left: 4px solid #FEC90F;
    }
    .border-success {
        border: 1px solid #13C672;
    }
</style>
@endsection
