@extends('layouts.theme')

@section('title', 'Fuentes de Datos - ' . $supplier->name)

@section('content')

    @include('managers.components.card', ['title' => 'Fuentes de Datos - ' . $supplier->name])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <!-- Sources Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Fuentes de datos</h5>
                        <p class="small mb-0 text-muted">Gestiona las fuentes de productos para {{ $supplier->name }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('source_type') || request('is_active'))
                            <a href="{{ route('settings.suppliers.sources.index', $supplier->uid) }}" class="btn btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @endif
                        <a href="{{ route('settings.suppliers.sources.create', $supplier->uid) }}" class="btn btn-primary">
                            Nueva fuente
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('settings.suppliers.sources.index', $supplier->uid) }}">
                    <div class="row align-items-center g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-magnifying-glass"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por nombre o descripción..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select select2" name="source_type" data-minimum-results-for-search="Infinity">
                                <option value="">Todos los tipos</option>
                                <option value="website" {{ request('source_type') == 'website' ? 'selected' : '' }}>Sitio Web</option>
                                <option value="ftp" {{ request('source_type') == 'ftp' ? 'selected' : '' }}>FTP</option>
                                <option value="sftp" {{ request('source_type') == 'sftp' ? 'selected' : '' }}>SFTP</option>
                                <option value="api" {{ request('source_type') == 'api' ? 'selected' : '' }}>API REST</option>
                                <option value="upload" {{ request('source_type') == 'upload' ? 'selected' : '' }}>Carga Manual</option>
                                <option value="file" {{ request('source_type') == 'file' ? 'selected' : '' }}>Archivo</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select select2" name="is_active" data-minimum-results-for-search="Infinity">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sources List -->
            <div class="card-body">
                @if($sources->count() > 0)
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-circle-info me-2"></i>
                        Las fuentes definen los orígenes de datos que se sincronizarán automáticamente
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Confianza</th>
                                    <th class="text-center">Prioridad</th>
                                    <th>Última conexión</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sources as $source)
                                    <tr>


                                        <td>
                                            <div>
                                                <strong>{{ $source->label }}</strong>
                                            </div>
                                        </td>

                                        <td>
                                            @php
                                                $typeBadges = [
                                                    'website' => ['bg' => 'light', 'icon' => 'globe', 'text' => 'Web'],
                                                    'ftp' => ['bg' => 'light', 'icon' => 'server', 'text' => 'FTP'],
                                                    'sftp' => ['bg' => 'light', 'icon' => 'shield', 'text' => 'SFTP'],
                                                    'api' => ['bg' => 'light', 'icon' => 'code', 'text' => 'API'],
                                                    'upload' => ['bg' => 'light', 'icon' => 'upload', 'text' => 'Upload'],
                                                    'file' => ['bg' => 'light', 'icon' => 'file', 'text' => 'Archivo'],
                                                ];
                                                $badge = $typeBadges[$source->source_type] ?? ['bg' => 'light', 'icon' => 'question', 'text' => $source->source_type];
                                            @endphp
                                            <span class="badge bg-{{ $badge['bg'] }}-subtle text-{{ $badge['bg'] }}">
                                                {{ $badge['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($source->description)
                                                <small class="text-muted">{{ Str::limit($source->description, 50) }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($source->is_active)
                                                <span class="badge bg-success-subtle text-success">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $trustColors = [
                                                    'high' => 'success',
                                                    'medium' => 'warning',
                                                    'low' => 'danger',
                                                ];
                                                $trustColor = $trustColors[$source->trust_level] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $trustColor }}-subtle text-{{ $trustColor }}">
                                                {{ ucfirst($source->trust_level) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-light-secondary text-info">
                                                {{ $source->priority }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($source->last_accessed_at)
                                                <small class="text-muted">{{ $source->last_accessed_at->format('d/m/Y H:i') }}</small>
                                            @else
                                                <small class="text-muted">Nunca</small>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('settings.suppliers.sources.edit', [$supplier->uid, $source->uid]) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item test-source" data-uid="{{ $source->uid }}">
                                                            Probar
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item confirm-delete" data-href="{{ route('settings.suppliers.sources.destroy', [$supplier->uid, $source->uid]) }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="round-48 rounded-circle bg-light-subtle text-muted mb-3 d-flex align-items-center justify-content-center">
                                <i class="fa fa-inbox fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay fuentes para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    No se encontraron resultados para "{{ request('search') }}"
                                @else
                                    Crea tu primera fuente de datos para comenzar
                                @endif
                            </p>
                            @if(!request('search'))
                                <a href="{{ route('settings.suppliers.sources.create', $supplier->uid) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus"></i> Crear primera fuente
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($sources->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $sources->firstItem() }}</strong> a <strong>{{ $sources->lastItem() }}</strong>
                            de <strong>{{ $sources->total() }}</strong> fuentes
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $sources->appends(request()->input())->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        allowClear: false,
        minimumResultsForSearch: Infinity
    });

    // Test connection
    $('.test-source').on('click', function() {
        const uid = $(this).data('uid');
        const btn = $(this);
        const originalHtml = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Probando...');

        $.ajax({
            url: '{{ route("settings.suppliers.sources.test", [$supplier->uid, ":uid"]) }}'.replace(':uid', uid),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Conexión exitosa', 'Prueba de Conexión');
                } else {
                    toastr.error(response.message || 'Conexión fallida', 'Prueba de Conexión');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al probar la conexión';
                toastr.error(message, 'Error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
