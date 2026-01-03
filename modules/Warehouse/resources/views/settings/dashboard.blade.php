@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Dashboard de Almacén'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                @if($selectedWarehouse)
                                    Dashboard: {{ $selectedWarehouse->name }}
                                @else
                                    Dashboard de Almacenes
                                @endif
                            </h5>
                        </div>
                        <div class="col-auto">
                            <select class="form-select" id="warehouse-selector">
                                <option value="">Seleccionar almacén</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->uid }}"
                                            {{ $selectedWarehouse && $selectedWarehouse->uid == $warehouse->uid ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if($selectedWarehouse)
                    <div class="card-body">
                        {{-- Estadísticas --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light-primary border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fa fa-warehouse fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Ubicaciones</h6>
                                                <h3 class="mb-0">{{ $statistics['total_locations'] ?? 0 }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-light-success border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fa fa-box fa-2x text-success"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Slots totales</h6>
                                                <h3 class="mb-0">{{ $statistics['total_slots'] ?? 0 }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-light-warning border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fa fa-check-circle fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Slots ocupados</h6>
                                                <h3 class="mb-0">{{ $statistics['occupied_slots'] ?? 0 }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-light-info border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fa fa-percentage fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Ocupación</h6>
                                                <h3 class="mb-0">{{ $statistics['occupancy_percentage'] ?? 0 }}%</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Movimientos recientes --}}
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Movimientos recientes</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($recentMovements && count($recentMovements) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Tipo</th>
                                                            <th>Slot</th>
                                                            <th>Producto</th>
                                                            <th>Cantidad</th>
                                                            <th>Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($recentMovements as $movement)
                                                            <tr>
                                                                <td>{{ $movement['type'] }}</td>
                                                                <td>{{ $movement['slot_address'] }}</td>
                                                                <td>{{ $movement['product'] }}</td>
                                                                <td>{{ $movement['quantity_delta'] }}</td>
                                                                <td>{{ $movement['recorded_at'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">No hay movimientos recientes</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Alertas --}}
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Alertas</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($alerts && count($alerts) > 0)
                                            @foreach($alerts as $alert)
                                                <div class="alert alert-{{ $alert['level'] }} alert-dismissible fade show mb-2" role="alert">
                                                    <small>{{ $alert['message'] }}</small>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted small">No hay alertas</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            Seleccione un almacén para ver su dashboard
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#warehouse-selector').on('change', function() {
        const warehouseUid = $(this).val();
        if (warehouseUid) {
            window.location.href = '/backups/warehouse/' + warehouseUid + '/details/dashboard';
        }
    });
});
</script>
@endpush
