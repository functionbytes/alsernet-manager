<div wire:ignore.self>
    <!-- Table Controls -->
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2 align-items-center">
            <select wire:model.live="filterStatus" class="form-select form-select-sm" style="width: auto;">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="running">En ejecución</option>
                <option value="completed">Completado</option>
                <option value="failed">Fallido</option>
            </select>
        </div>
        <div class="text-muted small">
            Mostrando <strong>{{ $syncStatuses->count() }}</strong> de <strong>{{ $syncStatuses->total() }}</strong>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light" style="background-color: #f9f9f9;">
                <tr>
                    <th class="text-muted small fw-semibold ps-4">
                        <a href="#" wire:click.prevent="sortBy('sync_type')" class="text-decoration-none">
                            Tipo
                            @if($sortBy === 'sync_type')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1" style="color: #90bb13;"></i>
                            @else
                                <i class="fas fa-sort ms-1 opacity-25"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-muted small fw-semibold">Estado</th>
                    <th class="text-muted small fw-semibold">Progreso</th>
                    <th class="text-muted small fw-semibold">
                        <a href="#" wire:click.prevent="sortBy('created_at')" class="text-decoration-none">
                            Iniciado
                            @if($sortBy === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1" style="color: #90bb13;"></i>
                            @else
                                <i class="fas fa-sort ms-1 opacity-25"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-muted small fw-semibold">Duración</th>
                    <th class="text-muted small fw-semibold text-center pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($syncStatuses as $sync)
                    <tr wire:key="sync-{{ $sync->id }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2" style="background-color: rgba(144, 187, 19, 0.1); width: 40px; height: 40px;">
                                    @switch($sync->sync_type)
                                        @case('product')
                                            <i class="fas fa-cube" style="color: #90bb13;"></i>
                                            @break
                                        @case('category')
                                            <i class="fas fa-sitemap" style="color: #90bb13;"></i>
                                            @break
                                        @case('price')
                                            <i class="fas fa-tag" style="color: #90bb13;"></i>
                                            @break
                                        @case('provider')
                                            <i class="fas fa-truck" style="color: #90bb13;"></i>
                                            @break
                                    @endswitch
                                </div>
                                <span class="fw-semibold">{{ ucfirst($sync->sync_type) }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background-color:
                                @if($sync->status === 'completed')
                                    #13C672
                                @elseif($sync->status === 'failed')
                                    #FA896B
                                @elseif($sync->status === 'running')
                                    #90bb13
                                @else
                                    #FEC90F
                                @endif
                            ">
                                {{ ucfirst($sync->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 4px; width: 80px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $sync->getProgressPercentage() }}%; background-color: #90bb13;"></div>
                                </div>
                                <small class="text-muted fw-semibold">{{ $sync->getProgressPercentage() }}%</small>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $sync->created_at->format('Y-m-d H:i') }}</small>
                        </td>
                        <td>
                            @if($sync->status === 'running')
                                <small class="text-muted">
                                    <i class="fas fa-hourglass-half text-warning"></i> En curso...
                                </small>
                            @elseif($sync->completed_at)
                                <small class="text-muted">
                                    {{ $sync->created_at->diffInSeconds($sync->completed_at) }}s
                                </small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                @if($sync->status === 'running')
                                    <button type="button" class="btn btn-outline-danger btn-sm" wire:click="cancel({{ $sync->id }})" title="Cancelar">
                                        <i class="fas fa-stop-circle"></i>
                                    </button>
                                @elseif($sync->status === 'failed')
                                    <button type="button" class="btn btn-outline-warning btn-sm" wire:click="retry({{ $sync->id }})" title="Reintentar">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="view({{ $sync->id }})" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-muted">
                            <i class="fas fa-inbox mb-2 d-block opacity-25" style="font-size: 2rem;"></i>
                            <small>No hay sincronizaciones registradas</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($syncStatuses->hasPages())
        <div class="p-4 border-top d-flex justify-content-center">
            {{ $syncStatuses->links() }}
        </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.class="opacity-50" class="transition-all">
        <div class="position-absolute top-50 start-50 translate-middle" wire:loading style="z-index: 10;">
            <div class="spinner-border" style="color: #90bb13;">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </div>
</div>
