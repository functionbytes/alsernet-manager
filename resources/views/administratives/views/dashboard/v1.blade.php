@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    @include('managers.includes.card', ['title' => 'Dashboard v1 - Cards Compactas'])

    <div class="row g-3">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Revenue Updates Style Card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="card-title fw-bold mb-0">Resumen de Documentos</h5>
                            <p class="card-subtitle text-muted small">Vista general del mes actual</p>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-primary rounded-circle" style="width: 10px; height: 10px;"></span>
                                <span class="text-muted small">Creados</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-success rounded-circle" style="width: 10px; height: 10px;"></span>
                                <span class="text-muted small">Aprobados</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="chart-revenue" style="height: 300px;"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted small mb-1">Total Este Mes</h6>
                                    <h3 class="fw-bold mb-0">{{ number_format($totalMonth) }}</h3>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-arrow-up me-1"></i>+{{ $totalToday }} hoy
                                        </span>
                                    </div>
                                </div>
                                <div class="p-3 bg-primary-subtle rounded">
                                    <h6 class="text-muted small mb-1">Pendientes</h6>
                                    <h3 class="fw-bold text-primary mb-0">{{ number_format($totalPending) }}</h3>
                                    <a href="{{ route('administrative.documents.pending') }}" class="small text-primary">Ver pendientes <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <div class="p-3 bg-success-subtle rounded">
                                    <h6 class="text-muted small mb-1">Aprobados</h6>
                                    <h3 class="fw-bold text-success mb-0">{{ number_format($totalApproved) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Documents --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Documentos Recientes</h6>
                        <a href="{{ route('administrative.documents') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Referencia</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentDocuments->take(5) as $doc)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-light-primary text-primary rounded-circle me-2">
                                            {{ strtoupper(substr($doc->customer_firstname ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 small fw-medium">{{ $doc->customer_firstname }} {{ $doc->customer_lastname }}</h6>
                                            <small class="text-muted">{{ $doc->order_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="small">{{ $doc->order_reference ?? '-' }}</span></td>
                                <td>
                                    @if($doc->status)
                                        <span class="badge" style="background-color: {{ $doc->status->color ?? '#6c757d' }}">{{ $doc->status->label }}</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</small></td>
                                <td>
                                    <a href="{{ route('administrative.documents.manage', $doc->uid) }}" class="btn btn-sm btn-light">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Yearly Breakup Style --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Distribución Anual</h6>
                    <div class="row align-items-center">
                        <div class="col-7">
                            <h3 class="fw-bold mb-1">{{ number_format($totalAll) }}</h3>
                            <p class="text-muted small mb-3">Documentos totales</p>
                            <div class="d-flex flex-column gap-2">
                                @foreach($statuses->take(4) as $status)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle" style="width: 8px; height: 8px; background-color: {{ $status->color ?? '#6c757d' }};"></span>
                                    <span class="small">{{ $status->label }}</span>
                                    <span class="small text-muted ms-auto">{{ $status->documents_count }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-5">
                            <div id="chart-breakup"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Earnings Style --}}
            <div class="card border-0 shadow-sm mb-3 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-white-50 small">Esta Semana</p>
                            <h3 class="fw-bold mb-0">{{ $totalWeek }}</h3>
                            <p class="mb-0 small text-white-50">documentos creados</p>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                    <div id="chart-weekly" class="mt-3"></div>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Estadísticas Rápidas</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary rounded p-2">
                                    <i class="fas fa-file-alt text-white"></i>
                                </div>
                                <span>Hoy</span>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $totalToday }}</h5>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-warning rounded p-2">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <span>Pendientes</span>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $totalPending }}</h5>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-danger rounded p-2">
                                    <i class="fas fa-times text-white"></i>
                                </div>
                                <span>Rechazados</span>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $totalRejected }}</h5>
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
$(function() {
    // Revenue Chart
    var revenueChart = new ApexCharts(document.querySelector("#chart-revenue"), {
        series: [{ name: "Documentos", data: @json($monthlyCounts) }],
        chart: { type: "area", height: 300, toolbar: { show: false }, sparkline: { enabled: false } },
        colors: ["#90bb13"],
        fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        stroke: { curve: "smooth", width: 2 },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($months), labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { formatter: val => Math.floor(val) } },
        grid: { borderColor: "#f1f1f1", strokeDashArray: 3 },
        tooltip: { y: { formatter: val => val + " docs" } }
    });
    revenueChart.render();

    // Breakup Donut
    var breakupChart = new ApexCharts(document.querySelector("#chart-breakup"), {
        series: @json($statuses->pluck('documents_count')),
        chart: { type: "donut", height: 150 },
        colors: @json($statuses->pluck('color')->map(fn($c) => $c ?? '#6c757d')),
        labels: @json($statuses->pluck('label')),
        legend: { show: false },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: "70%" } } }
    });
    breakupChart.render();

    // Weekly Mini Chart
    var weeklyChart = new ApexCharts(document.querySelector("#chart-weekly"), {
        series: [{ data: @json(array_slice($monthlyCounts, -4)) }],
        chart: { type: "bar", height: 60, sparkline: { enabled: true } },
        colors: ["rgba(255,255,255,0.5)"],
        plotOptions: { bar: { borderRadius: 3, columnWidth: "60%" } }
    });
    weeklyChart.render();
});
</script>
@endpush
@endsection
