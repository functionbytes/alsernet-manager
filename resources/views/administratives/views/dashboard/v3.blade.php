@extends('layouts.administratives')

@section('content')
<div class="container-fluid">

    @include('managers.includes.card', ['title' => 'Dashboard v3 - Minimal Style'])

    {{-- Top Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-file-alt text-primary fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Total</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalAll) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-hourglass-half text-warning fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Pendientes</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalPending) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3">
                            <i class="fas fa-check-double text-success fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Aprobados</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalApproved) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                            <i class="fas fa-ban text-danger fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Rechazados</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalRejected) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Main Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 fw-bold">Tendencia Anual</h5>
                            <p class="text-muted small mb-0">Documentos por mes</p>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-light active">Mes</button>
                            <button type="button" class="btn btn-light">Año</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="main-chart"></div>
                </div>
            </div>
        </div>

        {{-- Side Panel --}}
        <div class="col-lg-4">
            {{-- Period Stats --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Resumen del Período</h6>
                    <div class="row g-3">
                        <div class="col-4 text-center">
                            <div class="border rounded-3 p-3">
                                <i class="fas fa-sun text-warning mb-2"></i>
                                <h5 class="fw-bold mb-0">{{ $totalToday }}</h5>
                                <small class="text-muted">Hoy</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded-3 p-3">
                                <i class="fas fa-calendar-week text-info mb-2"></i>
                                <h5 class="fw-bold mb-0">{{ $totalWeek }}</h5>
                                <small class="text-muted">Semana</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded-3 p-3">
                                <i class="fas fa-calendar-alt text-primary mb-2"></i>
                                <h5 class="fw-bold mb-0">{{ $totalMonth }}</h5>
                                <small class="text-muted">Mes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Distribution --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Por Estado</h6>
                    <div id="donut-chart"></div>
                    <div class="mt-3">
                        @php $total = $statuses->sum('documents_count'); @endphp
                        @foreach($statuses as $status)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle" style="width: 10px; height: 10px; background-color: {{ $status->color ?? '#6c757d' }};"></span>
                                <span class="small">{{ $status->label }}</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold small">{{ $status->documents_count }}</span>
                                <span class="text-muted small">({{ $total > 0 ? round(($status->documents_count / $total) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold">Actividad Reciente</h5>
                    <a href="{{ route('administrative.documents') }}" class="btn btn-sm btn-primary">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Documento</th>
                                    <th class="border-0">Cliente</th>
                                    <th class="border-0">Tipo</th>
                                    <th class="border-0">Estado</th>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0 text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $doc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-file-alt text-muted"></i>
                                            <div>
                                                <span class="fw-medium">#{{ $doc->order_id }}</span>
                                                <br><small class="text-muted">{{ $doc->order_reference ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $doc->customer_firstname }} {{ $doc->customer_lastname }}</td>
                                    <td>
                                        @if($doc->documentLoad)
                                            <span class="badge bg-light text-dark border">{{ $doc->documentLoad->label }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->status)
                                            <span class="badge" style="background-color: {{ $doc->status->color ?? '#6c757d' }}">{{ $doc->status->label }}</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $doc->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('administrative.documents.manage', $doc->uid) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        Sin documentos recientes
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

    {{-- Quick Actions --}}
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <a href="{{ route('administrative.documents.pending') }}" class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Documentos Pendientes</h6>
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
                        <h6 class="mb-0 fw-bold text-dark">Importar Documentos</h6>
                        <small class="text-muted">Desde PrestaShop o ERP</small>
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
                        <h6 class="mb-0 fw-bold text-dark">Todos los Documentos</h6>
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
    // Main Chart - Line
    new ApexCharts(document.querySelector("#main-chart"), {
        series: [{ name: "Documentos", data: @json($monthlyCounts) }],
        chart: { type: "line", height: 350, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
        colors: ["#90bb13"],
        stroke: { curve: "smooth", width: 3 },
        markers: { size: 4, colors: ["#90bb13"], strokeColors: "#fff", strokeWidth: 2, hover: { size: 6 } },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($months), axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: '#6b7280' } } },
        yaxis: { labels: { style: { colors: '#6b7280' }, formatter: val => Math.floor(val) } },
        grid: { borderColor: "#f1f1f1", strokeDashArray: 4, padding: { left: 10, right: 10 } },
        tooltip: { y: { formatter: val => val + " documentos" } }
    }).render();

    // Donut Chart
    new ApexCharts(document.querySelector("#donut-chart"), {
        series: @json($statuses->pluck('documents_count')),
        chart: { type: "donut", height: 180, fontFamily: 'inherit' },
        colors: @json($statuses->pluck('color')->map(fn($c) => $c ?? '#6c757d')),
        labels: @json($statuses->pluck('label')),
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['#fff'] },
        plotOptions: { pie: { donut: { size: "65%" } } }
    }).render();
});
</script>
@endpush
@endsection
