@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Configuración de acciones de email por etapa'])

    <div class="widget-content">
        @include('managers.components.alerts')

        @if($configurations->isEmpty())
            <div class="alert alert-info mb-4">
                <i class="fa fa-info-circle me-2"></i>
                No hay configuraciones. Las configuraciones se crearán al editar cada etapa.
            </div>
        @endif

        <div class="row g-4">
            @foreach($availableStages as $stageKey => $stageName)
                <div class="col-md-6 col-lg-4">
                    <div class="card border h-100">
                        <div class="card-header bg-light border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="fa fa-layer-group me-2"></i>{{ $stageName }}
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            @php
                                $stageConfigs = $configurations->get($stageKey, collect());
                                $enabledCount = $stageConfigs->where('is_enabled', true)->count();
                                $totalCount = count($availableActions);
                            @endphp

                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fa fa-list me-1"></i>
                                    Acciones habilitadas: <strong>{{ $enabledCount }} de {{ $totalCount }}</strong>
                                </small>
                            </div>

                            @if($stageConfigs->isNotEmpty())
                                <div class="mb-3 flex-grow-1">
                                    <ul class="list-unstyled small">
                                        @foreach($stageConfigs->where('is_enabled', true) as $config)
                                            <li class="mb-2">
                                                <i class="fa fa-check text-success me-2"></i>
                                                {{ $availableActions[$config->email_action] ?? $config->email_action }}
                                            </li>
                                        @endforeach
                                        @if($stageConfigs->where('is_enabled', false)->count() > 0)
                                            <li class="text-muted mt-3 pt-2 border-top">
                                                <i class="fa fa-times text-danger me-2"></i>
                                                {{ $stageConfigs->where('is_enabled', false)->count() }} deshabilitadas
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            <a href="{{ route('manager.settings.stage-email-actions.edit', $stageKey) }}" class="btn btn-sm btn-primary w-100 mt-auto">
                                <i class="fa fa-edit me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection
