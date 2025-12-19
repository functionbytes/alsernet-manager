@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    @include('managers.includes.card', ['title' => 'Dashboard'])

    {{-- Top Stats Row - White Background --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Pendientes</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalPending) }}</h4>
                            <a href="{{ route('administrative.documents.pending') }}" class="badge bg-warning-subtle text-warning text-decoration-none">
                                Ver lista <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="pending-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Recibidos</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalReceived) }}</h4>
                            <span class="badge bg-info-subtle text-info">
                                Subidos
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="received-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Aprobados</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalApproved) }}</h4>
                            <span class="badge bg-success-subtle text-success">
                                Completados
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="approved-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Rechazados</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalRejected) }}</h4>
                            <span class="badge bg-danger-subtle text-danger">
                                No válidos
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="rejected-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Stats Row - White Background with Mini Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Total</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalAll) }}</h4>
                            <span class="badge bg-primary-subtle text-primary">
                                Documentos
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="total-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Hoy</p>
                            <h4 class="fw-bold text-dark mb-2">{{ $totalToday }}</h4>
                            <span class="badge bg-success-subtle text-success">
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
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Semana</p>
                            <h4 class="fw-bold text-dark mb-2">{{ $totalWeek }}</h4>
                            <span class="badge bg-info-subtle text-info">
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
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small fw-medium">Este Mes</p>
                            <h4 class="fw-bold text-dark mb-2">{{ number_format($totalMonth) }}</h4>
                            <span class="badge bg-warning-subtle text-warning">
                                {{ now()->format('F') }}
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="month-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Resumen de Documentos (from v1) + Distribución por Estado (from v3) --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="card-title fw-bold mb-0">Resumen de documentos</h5>
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
                                    <h6 class="text-muted small mb-1">Total este mes</h6>
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
        </div>

        <div class="col-lg-4">
            {{-- Distribución Anual (from v1) --}}
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Distribución anual</h6>
                            <p class="text-muted small mb-0">Por estado de documento</p>
                        </div>
                        <h3 class="fw-bold text-primary mb-0">{{ number_format($totalAll) }}</h3>
                    </div>
                    <div id="chart-breakup" style="margin: 0 auto;"></div>
                    <div class="d-flex flex-column gap-2 mt-3">
                        @foreach($statuses->take(5) as $status)
                        @php $total = $statuses->sum('documents_count'); @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background-color: {{ $status->color ?? '#6c757d' }}10;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle" style="width: 10px; height: 10px; background-color: {{ $status->color ?? '#6c757d' }};"></span>
                                <span class="small fw-medium">{{ $status->label }}</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold small">{{ number_format($status->documents_count) }}</span>
                                <span class="text-muted small ms-1">({{ $total > 0 ? round(($status->documents_count / $total) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- New Stats Components Row --}}
    <div class="row g-3 mb-4">
        {{-- Métodos de Carga --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Métodos de carga</h5>
                    <p class="card-subtitle mb-4">Origen de documentos</p>
                    <div class="position-relative">
                        @php
                            $loadIcons = [
                                'upload' => 'primary',
                                'sync' => 'success',
                                'database' => 'warning',
                                'cloud' => 'info',
                                'refresh' => 'primary',
                                'server' => 'success'
                            ];
                            $iconIndex = 0;
                            $colorKeys = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                        @endphp
                        @foreach($loads->take(4) as $index => $load)
                        @php
                            $colorKey = $colorKeys[$index % count($colorKeys)];
                            $iconName = ['upload', 'sync', 'database', 'cloud', 'refresh', 'server'][$index % 6];
                        @endphp
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex">
                                <div class="p-8 bg-{{ $colorKey }}-subtle rounded-2 d-flex align-items-center justify-content-center me-6">
                                    <i class="fas fa-{{ $iconName }} text-{{ $colorKey }} fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">{{ $load->label }}</h6>
                                    <p class="fs-3 mb-0 text-muted">Método de carga</p>
                                </div>
                            </div>
                            <h6 class="mb-0 fw-semibold">{{ number_format($load->count) }}</h6>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('administrative.documents') }}" class="btn btn-outline-primary w-100">Ver todos los documentos</a>
                </div>
            </div>
        </div>

        {{-- Estadísticas Semanales --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Estadísticas semanales</h5>
                    <p class="card-subtitle mb-0">Actividad de la semana</p>
                    <div id="weekly-stats-chart" class="mb-4 mt-3"></div>
                    <div class="position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex">
                                <div class="p-6 bg-primary-subtle text-primary rounded-2 me-6 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-chart-line fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Mayor Actividad</h6>
                                    <p class="fs-3 mb-0">{{ \Carbon\Carbon::now()->format('l') }}</p>
                                </div>
                            </div>
                            <div class="bg-primary-subtle text-primary badge">
                                <p class="fs-3 fw-semibold mb-0">+{{ $totalToday }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex">
                                <div class="p-6 bg-success-subtle text-success rounded-2 me-6 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-trophy fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Estado Principal</h6>
                                    <p class="fs-3 mb-0">{{ $statuses->first()->label ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="bg-success-subtle text-success badge">
                                <p class="fs-3 fw-semibold mb-0">{{ $statuses->first()->documents_count ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex">
                                <div class="p-6 bg-info-subtle text-info rounded-2 me-6 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-fire fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fs-4 fw-semibold">Más Usado</h6>
                                    <p class="fs-3 mb-0">Carga Manual</p>
                                </div>
                            </div>
                            <div class="bg-info-subtle text-info badge">
                                <p class="fs-3 fw-semibold mb-0">{{ $totalWeek }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tendencia Mensual --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title fw-semibold">Tendencia mensual</h5>
                        <p class="card-subtitle">Últimos 6 meses</p>
                        <div id="monthly-trend-chart" class="mb-4 mt-3"></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-2 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-file-alt fs-6"></i>
                                </div>
                                <div>
                                    <p class="fs-3 mb-0 fw-normal">Total</p>
                                    <h6 class="fw-semibold text-dark fs-4 mb-0">{{ number_format($totalAll) }}</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-success-subtle text-success rounded-2 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-check fs-6"></i>
                                </div>
                                <div>
                                    <p class="fs-3 mb-0 fw-normal">Aprobados</p>
                                    <h6 class="fw-semibold text-dark fs-4 mb-0">{{ number_format($totalApproved) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Documents Table (from v2 - most detailed) --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">Documentos recientes</h5>
                        <p class="text-muted small mb-0">Últimos documentos registrados en el sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('administrative.documents.import') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-import me-1"></i> Importar
                        </a>
                        <a href="{{ route('administrative.documents') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i> Ver Todos
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
                                    <div class="dropdown">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('administrative.documents.show', $doc->uid) }}">
                                                    <i class="fas fa-eye me-2"></i>Ver documento
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('administrative.documents.manage', $doc->uid) }}">
                                                    <i class="fas fa-edit me-2"></i>Gestionar
                                                </a>
                                            </li>
                                            @if($doc->status && $doc->status->key === 'pending')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-success" href="{{ route('administrative.documents.manage', $doc->uid) }}">
                                                    <i class="fas fa-check me-2"></i>Aprobar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="{{ route('administrative.documents.manage', $doc->uid) }}">
                                                    <i class="fas fa-times me-2"></i>Rechazar
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
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

    {{-- Quick Actions (from v3) --}}
    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('administrative.documents.pending') }}" class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Documentos pendientes</h6>
                        <small class="text-muted">{{ $totalPending }} por revisar</small>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('administrative.documents.import') }}" class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-file-import text-primary fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Importar documentos</h6>
                        <small class="text-muted">Desde prestashop o gestion</small>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('administrative.documents') }}" class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fas fa-list text-success fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Todos los documentos</h6>
                        <small class="text-muted">{{ $totalAll }} en total</small>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
    </div>

</div>

<style>
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.1)!important;
    transition: all .2s ease;
}
</style>

@push('scripts')
<script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script>
$(function() {
    // Revenue Chart (from v1) - Area with gradient
    new ApexCharts(document.querySelector("#chart-revenue"), {
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
    }).render();

    // Breakup Donut Chart (from v1) - Mejorado
    new ApexCharts(document.querySelector("#chart-breakup"), {
        series: @json($statuses->pluck('documents_count')),
        chart: {
            type: "donut",
            height: 240,
            fontFamily: 'inherit'
        },
        colors: @json($statuses->pluck('color')->map(fn($c) => $c ?? '#6c757d')),
        labels: @json($statuses->pluck('label')),
        legend: { show: false },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return Math.round(val) + "%";
            },
            style: {
                fontSize: '12px',
                fontWeight: '600',
                colors: ['#fff']
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 1,
                blur: 1,
                opacity: 0.5
            }
        },
        stroke: {
            width: 3,
            colors: ['#fff']
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "65%",
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#6c757d'
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontWeight: 'bold',
                            color: '#212529',
                            formatter: function(val) {
                                return parseInt(val);
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#6c757d',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " documentos";
                }
            }
        }
    }).render();

    // Load Donut Chart
    new ApexCharts(document.querySelector("#load-donut-chart"), {
        series: @json($loads->pluck('count')),
        chart: { type: "donut", height: 220, fontFamily: 'inherit' },
        colors: @json($loads->pluck('color')),
        labels: @json($loads->pluck('label')),
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['#fff'] },
        plotOptions: {
            pie: {
                donut: {
                    size: "70%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: () => '{{ $loads->sum("count") }}'
                        }
                    }
                }
            }
        }
    }).render();

    // Pendientes Mini Chart - Bar
    new ApexCharts(document.querySelector("#pending-chart"), {
        series: [{ data: [{{ $totalPending > 0 ? $totalPending : 1 }}, {{ max($totalPending - 50, 1) }}, {{ $totalPending }}, {{ max($totalPending - 20, 1) }}, {{ $totalPending + 10 }}] }],
        chart: { type: "bar", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        plotOptions: { bar: { borderRadius: 2, columnWidth: "60%" } }
    }).render();

    // Recibidos Mini Chart - Area
    new ApexCharts(document.querySelector("#received-chart"), {
        series: [{ data: [{{ $totalReceived }}, {{ max($totalReceived - 10, 0) }}, {{ $totalReceived + 5 }}, {{ $totalReceived }}, {{ $totalReceived + 3 }}] }],
        chart: { type: "area", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        fill: { type: "solid", opacity: 0.3 },
        stroke: { curve: "smooth", width: 2 }
    }).render();

    // Aprobados Mini Chart - Line
    new ApexCharts(document.querySelector("#approved-chart"), {
        series: [{ data: [{{ $totalApproved }}, {{ max($totalApproved - 2, 0) }}, {{ $totalApproved + 1 }}, {{ $totalApproved }}, {{ $totalApproved + 2 }}] }],
        chart: { type: "line", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        stroke: { curve: "smooth", width: 3 }
    }).render();

    // Rechazados Mini Chart - Bar
    new ApexCharts(document.querySelector("#rejected-chart"), {
        series: [{ data: [{{ $totalRejected }}, {{ max($totalRejected - 1, 0) }}, {{ $totalRejected }}, {{ max($totalRejected - 2, 0) }}, {{ $totalRejected + 1 }}] }],
        chart: { type: "bar", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb137a"],
        plotOptions: { bar: { borderRadius: 2, columnWidth: "60%" } }
    }).render();

    // Total Mini Chart - Line sparkline (Primary)
    new ApexCharts(document.querySelector("#total-chart"), {
        series: [{ data: @json($monthlyCounts) }],
        chart: { type: "line", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        stroke: { curve: "smooth", width: 2 },
        fill: { type: "gradient", gradient: { opacityFrom: 0.6, opacityTo: 0.1 } }
    }).render();

    // Today Mini Chart - Bar
    new ApexCharts(document.querySelector("#today-chart"), {
        series: [{ data: [{{ $totalToday > 0 ? $totalToday : 1 }}, {{ max($totalToday - 2, 1) }}, {{ $totalToday }}, {{ max($totalToday - 1, 1) }}, {{ $totalToday + 1 }}] }],
        chart: { type: "bar", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        plotOptions: { bar: { borderRadius: 2, columnWidth: "50%" } }
    }).render();

    // Week Mini Chart - Area
    new ApexCharts(document.querySelector("#week-chart"), {
        series: [{ data: @json(array_slice($monthlyCounts, -5)) }],
        chart: { type: "area", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb13"],
        fill: { type: "solid", opacity: 0.3 },
        stroke: { curve: "smooth", width: 2 }
    }).render();

    // Month Mini Chart - Bar
    new ApexCharts(document.querySelector("#month-chart"), {
        series: [{ data: @json(array_slice($monthlyCounts, -6)) }],
        chart: { type: "bar", height: 70, width: 80, sparkline: { enabled: true } },
        colors: ["#90bb137a"],
        plotOptions: { bar: { borderRadius: 2, columnWidth: "50%" } }
    }).render();

    // Weekly Stats Chart - Area chart
    new ApexCharts(document.querySelector("#weekly-stats-chart"), {
        series: [{ name: "Documentos", data: @json(array_slice($monthlyCounts, -4)) }],
        chart: { type: "area", height: 120, sparkline: { enabled: false }, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ["#90bb13"],
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.18,
                opacityTo: 0,
                stops: [0.2, 1.8, 1]
            }
        },
        stroke: { curve: "smooth", width: 2 },
        dataLabels: { enabled: false },
        grid: { show: false },
        xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { show: false } },
        tooltip: { y: { formatter: val => val + " documentos" } }
    }).render();

    // Monthly Trend Chart - Bar chart
    new ApexCharts(document.querySelector("#monthly-trend-chart"), {
        series: [{ name: "Documentos", data: @json(array_slice($monthlyCounts, -6)) }],
        chart: { type: "bar", height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ["#90bb13"],
        plotOptions: {
            bar: {
                borderRadius: 3,
                columnWidth: "50%",
                distributed: false,
                colors: {
                    ranges: [{
                        from: 0,
                        to: 100000,
                        color: "#90bb1320"
                    }],
                    backgroundBarColors: [],
                    backgroundBarOpacity: 1
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
        grid: { show: false },
        xaxis: {
            categories: @json(array_slice($months, -6)),
            labels: { style: { colors: '#adb0bb', fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: { show: false },
        tooltip: { y: { formatter: val => val + " documentos" } }
    }).render();
});
</script>
@endpush
@endsection
