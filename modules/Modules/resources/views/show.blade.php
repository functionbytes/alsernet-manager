@extends('layouts.theme')

@section('title', 'Detalles: ' . $module['name'])

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body">
                <div class="d-flex no-block align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-cube me-2"></i>{{ $module['name'] }}
                    </h5>
                    <a href="{{ route('modules.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>

                <p class="card-subtitle mb-4">
                    {{ $module['description'] ?: 'Sin descripción disponible' }}
                </p>

                {{-- Module Information Section --}}
                <div class="mb-4">
                    <h6 class="mb-3 text-uppercase fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Información general
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Alias</label>
                            <div class="fw-bold">
                                <code class="text-primary">{{ $module['alias'] }}</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Versión</label>
                            <div>
                                <span class="badge bg-info">{{ $module['version'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Prioridad</label>
                            <div>
                                <span class="badge bg-secondary">{{ $module['priority'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Estado</label>
                            <div>
                                @if($module['enabled'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Technical Information Section --}}
                <hr class="my-4">

                <div class="mb-4">
                    <h6 class="mb-3 text-uppercase fw-bold">
                        <i class="fas fa-code me-2"></i>Información técnica
                    </h6>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small">Ruta</label>
                            <div class="bg-light p-2 rounded small text-break">
                                <code>{{ $module['path'] }}</code>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small">Namespace</label>
                            <div class="bg-light p-2 rounded small">
                                <code>{{ $module['namespace'] }}</code>
                            </div>
                        </div>
                    </div>

                    @if(!empty($module['providers']))
                    <div class="mt-3">
                        <label class="form-label text-muted small">Service Providers</label>
                        <div class="bg-light p-2 rounded">
                            @foreach($module['providers'] as $provider)
                                <div class="small mb-2">
                                    <code class="text-dark">{{ $provider }}</code>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($module['keywords']))
                    <div class="mt-3">
                        <label class="form-label text-muted small">Keywords</label>
                        <div class="mb-2">
                            @foreach($module['keywords'] as $keyword)
                                <span class="badge bg-light text-dark">{{ $keyword }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Actions Section --}}
                <hr class="my-4">

                <div class="mb-3">
                    <h6 class="mb-3 text-uppercase fw-bold">
                        <i class="fas fa-cogs me-2"></i>Acciones
                    </h6>

                    <div class="btn-group" role="group">
                        @if(!in_array($module['name'], ['Role', 'Modules']))
                            @if($module['enabled'])
                                <form action="{{ route('modules.disable', $module['alias']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('¿Deshabilitar {{ $module['name'] }}?')">
                                        <i class="fas fa-pause me-2"></i>Deshabilitar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('modules.enable', $module['alias']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-play me-2"></i>Habilitar
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('modules.uninstall', $module['alias']) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Desinstalar {{ $module['name'] }}? Esta acción es irreversible.')">
                                    <i class="fas fa-trash me-2"></i>Desinstalar
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lock me-2"></i>Este módulo está protegido y no puede ser modificado.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
