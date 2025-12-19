@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    @include('managers.includes.card', ['title' => 'Dashboard v2 - Modernize Style'])

    <div class="row g-3">
        {{-- Revenue Updates --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-sm-flex d-block align-items-center justify-content-between mb-4">
                        <div class="mb-3 mb-sm-0">
                            <h5 class="card-title fw-bold">Actividad de Documentos</h5>
                            <p class="card-subtitle text-muted mb-0">Resumen mensual de documentos</p>
                        </div>
                        <div>
                            <select class="form-select form-select-sm">
                                <option selected>Este Mes</option>
                                <option>Mes Anterior</option>
                                <option>Últimos 3 Meses</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div id="revenue-chart"></div>
                        </div>
                        <div class="col-lg-4">
                            <div class="row">
                                <div class="col-6 col-lg-12 mb-3">
                                    <div class="bg-light-primary rounded-3 p-3">
                                        <h6 class="text-primary mb-1 small">Total Mes</h6>
                                        <h4 class="fw-bold text-primary mb-0">{{ number_format($totalMonth) }}</h4>
                                        <span class="text-muted small">documentos</span>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-12 mb-3">
                                    <div class="bg-light-success rounded-3 p-3">
                                        <h6 class="text-success mb-1 small">Aprobados</h6>
                                        <h4 class="fw-bold text-success mb-0">{{ number_format($totalApproved) }}</h4>
                                        <span class="text-muted small">completados</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <a href="{{ route('administrative.documents') }}" class="btn btn-primary">
                                            Ver Todos <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yearly Breakup --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body pb-0">
                    <h5 class="card-title fw-bold">Distribución por Estado</h5>
                    <p class="card-subtitle text-muted mb-0">Total: {{ number_format($totalAll) }} documentos</p>
                </div>
                <div id="breakup-chart" class="mx-n2"></div>
                <div class="card-body pt-0">
                    <div class="row text-center">
                        @foreach($statuses->take(3) as $status)
                        <div class="col-4">
                            <span class="d-block small text-muted">{{ $status->label }}</span>
                            <h6 class="fw-bold mb-0">{{ $status->documents_count }}</h6>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-primary overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-white-50 mb-1 small fw-medium">Hoy</p>
                            <h4 class="fw-bold text-white mb-2">{{ $totalToday }}</h4>
                            <span class="badge bg-white bg-opacity-25 text-white">
                                <i class="fas fa-arrow-up me-1"></i>Nuevos
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="today-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-warning overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-dark mb-1 small fw-medium opacity-75">Pendientes</p>
                            <h4 class="fw-bold text-dark mb-2">{{ $totalPending }}</h4>
                            <a href="{{ route('administrative.documents.pending') }}" class="badge bg-dark bg-opacity-25 text-dark text-decoration-none">
                                Ver lista <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-3x text-dark opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-success overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-white-50 mb-1 small fw-medium">Semana</p>
                            <h4 class="fw-bold text-white mb-2">{{ $totalWeek }}</h4>
                            <span class="badge bg-white bg-opacity-25 text-white">
                                Documentos
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="week-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-danger overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-white-50 mb-1 small fw-medium">Rechazados</p>
                            <h4 class="fw-bold text-white mb-2">{{ $totalRejected }}</h4>
                            <span class="badge bg-white bg-opacity-25 text-white">
                                Este mes
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle fa-3x text-white opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Documents Table --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">Documentos Recientes</h5>
                        <p class="text-muted small mb-0">Últimos documentos registrados en el sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('administrative.documents.import') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-import me-1"></i> Importar
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="border-0 ps-4">Cliente</th>
                                <th class="border-0">Orden</th>
                                <th class="border-0">Origen</th>
                                <th class="border-0">Estado</th>
                                <th class="border-0">Fecha</th>
                                <th class="border-0 text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDocuments as $doc)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light-primary d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <span class="fw-bold text-primary">{{ strtoupper(substr($doc->customer_firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($doc->customer_lastname ?? '', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">{{ $doc->customer_firstname }} {{ $doc->customer_lastname }}</h6>
                                            <small class="text-muted">{{ $doc->customer_email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">#{{ $doc->order_id }}</span>
                                    <br><small class="text-muted">{{ $doc->order_reference ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($doc->documentLoad)
                                        <span class="badge bg-light text-dark">{{ $doc->documentLoad->label }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($doc->status)
                                        <span class="badge" style="background-color: {{ $doc->status->color ?? '#6c757d' }}">{{ $doc->status->label }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sin estado</span>
                                    @endif
                                </td>
                                <td>
                                    <span>{{ $doc->created_at->format('d M Y') }}</span>
                                    <br><small class="text-muted">{{ $doc->created_at->format('H:i') }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('administrative.documents.show', $doc->uid) }}" class="btn btn-sm btn-light" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('administrative.documents.manage', $doc->uid) }}" class="btn btn-sm btn-primary" title="Gestionar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                                        <p class="mb-0">No hay documentos recientes</p>
                                    </div>
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
$(function() {
    // Revenue Chart - Area
    new ApexCharts(document.querySelector("#revenue-chart"), {
        series: [{
            name: "Documentos",
            data: @json($monthlyCounts)
        }],
        chart: { type: "area", height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ["#90bb13"],
        fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] } },
        stroke: { curve: "smooth", width: 2 },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($months), axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { formatter: val => Math.floor(val) } },
        grid: { borderColor: "#f1f1f1", strokeDashArray: 3, padding: { left: 0, right: 0 } },
        tooltip: { y: { formatter: val => val + " documentos" } }
    }).render();

    // Breakup Chart - Donut
    new ApexCharts(document.querySelector("#breakup-chart"), {
        series: @json($statuses->pluck('documents_count')),
        chart: { type: "donut", height: 200, fontFamily: 'inherit' },
        colors: @json($statuses->pluck('color')->map(fn($c) => $c ?? '#6c757d')),
        labels: @json($statuses->pluck('label')),
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
        plotOptions: { pie: { donut: { size: "75%", labels: { show: true, name: { show: true }, value: { show: true, fontSize: '24px', fontWeight: 700 }, total: { show: true, label: 'Total', formatter: () => '{{ $totalAll }}' } } } } }
    }).render();

    // Today Mini Chart
    new ApexCharts(document.querySelector("#today-chart"), {
        series: [{ data: [{{ $totalToday > 0 ? $totalToday : 1 }}, {{ max($totalToday - 2, 1) }}, {{ $totalToday }}, {{ max($totalToday - 1, 1) }}, {{ $totalToday + 1 }}] }],
        chart: { type: "bar", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["rgba(255,255,255,0.6)"],
        plotOptions: { bar: { borderRadius: 2, columnWidth: "50%" } }
    }).render();

    // Week Mini Chart
    new ApexCharts(document.querySelector("#week-chart"), {
        series: [{ data: @json(array_slice($monthlyCounts, -5)) }],
        chart: { type: "area", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["rgba(255,255,255,0.6)"],
        fill: { type: "solid", opacity: 0.3 },
        stroke: { curve: "smooth", width: 2 }
    }).render();
});
</script>
@endpush
@endsection
