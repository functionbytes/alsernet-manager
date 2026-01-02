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
                    <h6 class="mb-3 fw-bold border-bottom pb-2">
                        Información general
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-1">Alias</label>
                                <div class="fw-bold">
                                    <code class="text-primary fs-6">{{ $module['alias'] }}</code>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-1">Versión</label>
                                <div>
                                    <span class="badge bg-light text-dark border fs-6">v{{ $module['version'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-1">Prioridad</label>
                                <div>
                                    <span class="badge bg-primary fs-6">{{ $module['priority'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-1">Estado</label>
                                <div>
                                    @if($module['enabled'])
                                        <span class="badge bg-success-subtle text-success border border-success fs-6">
                                            <i class="fas fa-circle fa-2xs me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary fs-6">
                                            <i class="fas fa-circle fa-2xs me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Technical Information Section --}}
                <hr class="my-4">

                <div class="mb-4">
                    <h6 class="mb-3 fw-bold border-bottom pb-2">
                        Información técnica
                    </h6>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-2">Ruta del módulo</label>
                                <div class="text-break">
                                    <code class="text-dark">{{ $module['path'] }}</code>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted small mb-2">Namespace</label>
                                <div>
                                    <code class="text-dark">{{ $module['namespace'] }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($module['providers']))
                    <div class="mt-3">
                        <div class="p-3 bg-light rounded">
                            <label class="form-label text-muted small mb-2">Service Providers</label>
                            <div class="d-flex flex-column gap-2">
                                @foreach($module['providers'] as $provider)
                                    <div class="badge bg-white text-dark border text-start">
                                        <code class="text-dark">{{ $provider }}</code>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!empty($module['keywords']))
                    <div class="mt-3">
                        <div class="p-3 bg-light rounded">
                            <label class="form-label text-muted small mb-2">Keywords</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($module['keywords'] as $keyword)
                                    <span class="badge bg-primary-subtle text-primary border border-primary">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Actions Section --}}
                <hr class="my-4">

                <div class="mb-3">
                    <h6 class="mb-3 fw-bold border-bottom pb-2">
                        Acciones disponibles
                    </h6>

                    <div class="d-flex gap-2 flex-wrap">
                        {{-- Edit Button --}}
                        <a href="{{ route('modules.edit', $module['alias']) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar configuración
                        </a>

                        @if(!in_array($module['name'], ['Role', 'Modules']))
                            @if($module['enabled'])
                                <form action="{{ route('modules.disable', $module['alias']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('¿Deshabilitar {{ $module['name'] }}?')">
                                        <i class="fas fa-pause me-2"></i>Deshabilitar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('modules.enable', $module['alias']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-play me-2"></i>Habilitar
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('modules.uninstall', $module['alias']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Desinstalar {{ $module['name'] }}? Esta acción es irreversible.')">
                                    <i class="fas fa-trash-alt me-2"></i>Desinstalar
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning mb-0 d-inline-flex align-items-center">
                                <i class="fas fa-shield-alt me-2"></i>
                                <span>Este módulo está protegido y no puede ser deshabilitado o desinstalado.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
