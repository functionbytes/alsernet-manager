@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    @include('managers.includes.card', ['title' => 'Dashboard'])

    <div class="widget-content searchable-container list">

        {{-- Stats Cards Row --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-primary-subtle rounded">
                                    <i class="fas fa-file-alt text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0 fw-bold">{{ number_format($totalAll) }}</h3>
                                <p class="text-muted mb-0 small">Total documentos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-warning-subtle rounded">
                                    <i class="fas fa-clock text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0 fw-bold">{{ number_format($totalPending) }}</h3>
                                <p class="text-muted mb-0 small">Pendientes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-info-subtle rounded">
                                    <i class="fas fa-inbox text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0 fw-bold">{{ number_format($totalReceived) }}</h3>
                                <p class="text-muted mb-0 small">Recibidos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-success-subtle rounded">
                                    <i class="fas fa-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0 fw-bold">{{ number_format($totalApproved) }}</h3>
                                <p class="text-muted mb-0 small">Aprobados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50 small">Hoy</p>
                                <h4 class="mb-0 fw-bold">{{ $totalToday }}</h4>
                            </div>
                            <i class="fas fa-calendar-day fs-3 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50 small">Esta semana</p>
                                <h4 class="mb-0 fw-bold">{{ $totalWeek }}</h4>
                            </div>
                            <i class="fas fa-calendar-week fs-3 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50 small">Este mes</p>
                                <h4 class="mb-0 fw-bold">{{ $totalMonth }}</h4>
                            </div>
                            <i class="fas fa-calendar-alt fs-3 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50 small">Rechazados</p>
                                <h4 class="mb-0 fw-bold">{{ $totalRejected }}</h4>
                            </div>
                            <i class="fas fa-times-circle fs-3 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Chart Section --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Tendencia de Documentos</h6>
                                <small class="text-muted">Documentos creados por mes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart"></div>
                    </div>
                </div>
            </div>

            {{-- Status Distribution --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Distribución por Estado</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @php $totalDocs = $statuses->sum('documents_count'); @endphp
                            @forelse($statuses as $status)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="badge me-2" style="background-color: {{ $status->color ?? '#6c757d' }}; width: 10px; height: 10px; padding: 0;"></span>
                                        <span>{{ $status->label }}</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold">{{ $status->documents_count }}</span>
                                        @if($totalDocs > 0)
                                            <small class="text-muted ms-1">({{ round(($status->documents_count / $totalDocs) * 100, 1) }}%)</small>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    No hay estados configurados
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Documents --}}
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Documentos Recientes</h6>
                                <small class="text-muted">Últimos 10 documentos registrados</small>
                            </div>
                            <a href="{{ route('administrative.documents') }}" class="btn btn-sm btn-primary">
                                Ver todos
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Orden ID</th>
                                        <th>Referencia</th>
                                        <th>Cliente</th>
                                        <th>Origen</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentDocuments as $document)
                                        <tr>
                                            <td>
                                                <strong>{{ $document->order_id }}</strong>
                                            </td>
                                            <td>
                                                {{ $document->order_reference ?? '-' }}
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="fw-medium">{{ $document->customer_firstname }} {{ $document->customer_lastname }}</span>
                                                    @if($document->customer_email)
                                                        <br><small class="text-muted">{{ $document->customer_email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($document->documentLoad)
                                                    <span class="badge bg-light-info text-info">{{ $document->documentLoad->label }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($document->status)
                                                    <span class="badge" style="background-color: {{ $document->status->color ?? '#6c757d' }}">
                                                        {{ $document->status->label }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span>{{ $document->created_at->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('administrative.documents.show', $document->uid) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('administrative.documents.manage', $document->uid) }}" class="btn btn-sm btn-outline-primary" title="Gestionar">
                                                    <i class="fas fa-cog"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
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
                height: 350,
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
