@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Eventos de webhooks</h1>
            <p class="text-muted small mb-0">Consulta los tipos de eventos disponibles</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row gap-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" placeholder="Buscar evento..." name="search">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>En construcción:</strong> Esta sección se completará una vez que la tabla webhook_events esté lista.
    </div>

    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-alt fa-2x text-muted mb-3 d-block opacity-50"></i>
            <p class="text-muted mb-0">No hay tipos de eventos disponibles</p>
            <small class="text-muted d-block mt-2">Los eventos aparecerán aquí cuando se registren en el sistema</small>
        </div>
    </div>
</div>
@endsection
