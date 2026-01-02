@extends('layouts.theme')

@section('title', 'Detalles del módulo: ' . $module['name'])

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cube me-2"></i>Detalles del módulo
        </h1>
        <a href="{{ route('modules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    {{-- Module Information --}}
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información general</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Nombre del módulo</label>
                    <div class="h5">{{ $module['name'] }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Alias</label>
                    <div class="h5">
                        <code>{{ $module['alias'] }}</code>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Versión</label>
                    <div class="h5">
                        <span class="badge bg-info">{{ $module['version'] }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Prioridad</label>
                    <div class="h5">
                        <span class="badge bg-secondary">{{ $module['priority'] }}</span>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label text-muted">Descripción</label>
                    <p class="lead">{{ $module['description'] ?? 'Sin descripción' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Section --}}
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Estado del módulo</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label text-muted">Estado actual</label>
                    @if($module['enabled'])
                        <div class="h5">
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Habilitado
                            </span>
                        </div>
                        <p class="text-muted small">Este módulo está activo y cargado en el sistema.</p>
                    @else
                        <div class="h5">
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i>Deshabilitado
                            </span>
                        </div>
                        <p class="text-muted small">Este módulo está desactivado.</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Acciones disponibles</label>
                    <div class="btn-group d-flex" role="group">
                        @if(!in_array($module['name'], ['Role', 'Modules']))
                            @if($module['enabled'])
                                <form action="{{ route('modules.disable', $module['alias']) }}" method="POST" class="w-50 me-2">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100"
                                            onclick="return confirm('¿Desabilitar el módulo?')">
                                        <i class="fas fa-times me-2"></i>Deshabilitar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('modules.enable', $module['alias']) }}" method="POST" class="w-50 me-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-2"></i>Habilitar
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('modules.uninstall', $module['alias']) }}" method="POST" class="w-50">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('¿Desinstalar este módulo? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash me-2"></i>Desinstalar
                                </button>
                            </form>
                        @else
                            <span class="text-muted">Este módulo es protegido y no puede ser modificado.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Technical Information --}}
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información técnica</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Ruta del módulo</label>
                    <code class="d-block p-2 bg-light rounded">{{ $module['path'] }}</code>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Namespace</label>
                    <code class="d-block p-2 bg-light rounded">{{ $module['namespace'] }}</code>
                </div>
            </div>

            @if(!empty($module['providers']))
            <div class="mb-3">
                <label class="form-label text-muted">Service Providers</label>
                <div class="bg-light p-3 rounded">
                    @foreach($module['providers'] as $provider)
                        <div class="mb-2">
                            <code>{{ $provider }}</code>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($module['aliases']))
            <div class="mb-3">
                <label class="form-label text-muted">Aliases</label>
                <div class="bg-light p-3 rounded">
                    @foreach($module['aliases'] as $alias => $class)
                        <div class="mb-2">
                            <strong>{{ $alias }}</strong> → <code>{{ $class }}</code>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
