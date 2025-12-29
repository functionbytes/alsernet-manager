@extends('layouts.managers')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $event->title }}</h5>
            <div class="gap-2">
                <a href="{{ route('manager.events.edit', $event->uid) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="{{ route('manager.events') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha de inicio</label>
                    <p>{{ $event->start_at?->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha de finalización</label>
                    <p>{{ $event->end_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Estado</label>
                    <p>
                        @if($event->available)
                            <span class="badge bg-success">Público</span>
                        @else
                            <span class="badge bg-danger">Oculto</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Color</label>
                    <div style="width: 50px; height: 40px; background-color: {{ $event->color_flag ?? '#90bb13' }}; border-radius: 4px;"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Destacado</label>
                    <p>{{ $event->featured ? 'Sí' : 'No' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Oferta especial</label>
                    <p>{{ $event->amazing ? 'Sí' : 'No' }}</p>
                </div>
            </div>

            @if($event->iva)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">IVA</label>
                        <p>{{ $event->iva }}%</p>
                    </div>
                </div>
            @endif

            <hr>

            <p class="text-muted small">
                <i class="fas fa-calendar me-1"></i>
                Creado: {{ $event->created_at?->format('d/m/Y H:i') }}
                @if($event->updated_at && $event->updated_at != $event->created_at)
                    <br>
                    Actualizado: {{ $event->updated_at?->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
