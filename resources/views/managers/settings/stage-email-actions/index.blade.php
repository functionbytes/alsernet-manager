@extends('layouts.managers.app')

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración de acciones de email por etapa</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if($configurations->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay configuraciones. Las configuraciones se crearán al editar cada etapa.
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($availableStages as $stageKey => $stageName)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-bold">{{ $stageName }}</h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $stageConfigs = $configurations->get($stageKey, collect());
                                                $enabledCount = $stageConfigs->where('is_enabled', true)->count();
                                                $totalCount = count($availableActions);
                                            @endphp

                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="fas fa-list me-1"></i>
                                                    Acciones habilitadas: <strong>{{ $enabledCount }} de {{ $totalCount }}</strong>
                                                </small>
                                            </div>

                                            @if($stageConfigs->isNotEmpty())
                                                <div class="mb-3">
                                                    <ul class="list-unstyled small">
                                                        @foreach($stageConfigs->where('is_enabled', true) as $config)
                                                            <li class="mb-2">
                                                                <i class="fas fa-check text-success me-2"></i>
                                                                {{ $availableActions[$config->email_action] ?? $config->email_action }}
                                                            </li>
                                                        @endforeach
                                                        @if($stageConfigs->where('is_enabled', false)->count() > 0)
                                                            <li class="text-muted mt-3 pt-2 border-top">
                                                                <i class="fas fa-times text-danger me-2"></i>
                                                                {{ $stageConfigs->where('is_enabled', false)->count() }} deshabilitadas
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif

                                            <a href="{{ route('manager.settings.stage-email-actions.edit', $stageKey) }}" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-edit me-2"></i>Configurar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
