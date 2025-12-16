@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb Card --}}
    <div class="card position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12 col-md-9">
                    <h6 class="fw-semibold mb-1 text-uppercase">Panel de Control</h6>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="{{ url('/home') }}">Dashboard</a></li>
                        </ol>
                    </nav>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="text-center mb-n5">
                        <img src="./images/breadcrumb/ChatBc.png" alt="" class="img-fluid mb-n4">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="widget-content searchable-container list">

        {{-- Main Dashboard Card --}}
        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Resumen General</h5>
                        <p class="small mb-0 text-muted">Vista general del estado de documentos y actividad reciente</p>
                    </div>
                </div>
            </div>

            {{-- Main Stats Cards --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">
                                            Hoy
                                        </h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalToday }}</h4>
                                        <small class="text-muted">Documentos creados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">
                                            Esta Semana
                                        </h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalWeek }}</h4>
                                        <small class="text-muted">Documentos creados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-info mb-2">
                                            Este Mes
                                        </h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalMonth }}</h4>
                                        <small class="text-muted">Documentos creados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-warning mb-2">
                                            Pendientes
                                        </h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalPending }}</h4>
                                        <small class="text-muted">Por procesar</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart & Recent Documents Section --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    {{-- Document Trends Chart --}}
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">Tendencia de Documentos</h6>
                                    <p class="text-muted small mb-0">Estadísticas mensuales del año actual</p>
                                </div>
                                <select class="form-select" style="width: auto;">
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                </select>
                            </div>
                            <div id="chart"></div>
                        </div>
                    </div>

                    {{-- Recent Documents --}}
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <h6 class="mb-3 fw-bold">Documentos Recientes</h6>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentDocuments as $doc)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong class="d-block">{{ $doc->customer_firstname }} {{ $doc->customer_lastname }}</strong>
                                                        <small class="text-muted d-block">{{ $doc->type }}</small>
                                                        <small class="text-muted">{{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($doc->proccess == 'pending')
                                                        <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                                                    @elseif($doc->proccess == 'completed' || $doc->proccess == 'approved')
                                                        <span class="badge bg-success-subtle text-success">Completado</span>
                                                    @elseif($doc->proccess == 'rejected')
                                                        <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($doc->proccess) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    No hay documentos recientes
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Statistics Table --}}
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="mb-1 fw-bold">Estadísticas por Estado</h6>
                    <p class="text-muted small mb-0">Distribución de documentos según su estado de procesamiento</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="30%">Estado</th>
                                <th width="15%">Total</th>
                                <th width="55%">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDocs = $statistics->sum('total');
                            @endphp
                            @forelse($statistics as $stat)
                                <tr>
                                    <td>
                                        @if($stat->proccess == 'pending')
                                            <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                                        @elseif($stat->proccess == 'completed' || $stat->proccess == 'approved')
                                            <span class="badge bg-success-subtle text-success">Completado</span>
                                        @elseif($stat->proccess == 'rejected')
                                            <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                                        @elseif($stat->proccess == 'incomplete')
                                            <span class="badge bg-warning-subtle text-warning">Incompleto</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($stat->proccess ?: 'Sin estado') }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $stat->total }}</strong></td>
                                    <td>
                                        @if($totalDocs > 0)
                                            <div class="d-flex align-items-center gap-3">
                                                <span style="min-width: 45px; font-weight: 500;">
                                                    {{ round(($stat->total / $totalDocs) * 100, 1) }}%
                                                </span>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($stat->total / $totalDocs) * 100 }}%" aria-valuenow="{{ ($stat->total / $totalDocs) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                        No hay datos disponibles
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script>
    $(function () {
        var chart = {
            series: [
                { name: "Documentos", data: @json($monthlyCounts) },
            ],
            chart: {
                type: "bar",
                height: 320,
                toolbar: { show: true },
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            },
            colors: ["#90bb13"],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "50%",
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            grid: {
                borderColor: "#f3f4f6",
                strokeDashArray: 3,
                xaxis: { lines: { show: false } },
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 10
                }
            },
            xaxis: {
                categories: @json($months),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#6b7280',
                        fontSize: '12px',
                        fontWeight: 500
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6b7280',
                        fontSize: '12px',
                        fontWeight: 500
                    },
                    formatter: function(val) {
                        return Math.floor(val);
                    }
                },
            },
            tooltip: {
                theme: "light",
                style: {
                    fontSize: '12px',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif'
                },
                y: {
                    formatter: function(val) {
                        return val + " documentos";
                    }
                }
            },
        };

        var chartElement = new ApexCharts(document.querySelector("#chart"), chart);
        chartElement.render();
    });
</script>
@endpush
@endsection
