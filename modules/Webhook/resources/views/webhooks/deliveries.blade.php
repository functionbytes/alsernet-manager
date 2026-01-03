@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Entregas de webhooks</h1>
            <p class="text-muted small mb-0">Monitorea el estado de las entregas de webhooks</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row gap-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Buscar por evento..." name="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Todos los estados</option>
                        <option value="success">Exitoso</option>
                        <option value="pending">Pendiente</option>
                        <option value="failed">Fallido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="integration">
                        <option value="">Todas las integraciones</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>En construcción:</strong> Esta sección se completará una vez que la tabla webhook_deliveries esté lista.
    </div>

    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-box-open fa-2x text-muted mb-3 d-block opacity-50"></i>
            <p class="text-muted mb-0">No hay registros de entregas disponibles</p>
            <small class="text-muted d-block mt-2">Los registros aparecerán aquí cuando se procesen entregas</small>
        </div>
    </div>
</div>
@endsection
