@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Eventos</h5>
            <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i>Nuevo evento
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Buscar eventos..." value="{{ $searchKey }}">
                    </div>
                    <div class="col-md-3">
                        <select name="available" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" @selected($available == 1)>Públicos</option>
                            <option value="0" @selected($available == 0)>Ocultos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Inicio</th>
                            <th>Finalización</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>
                                    <a href="{{ route('events.view', $event->uid) }}" class="text-decoration-none">
                                        {{ $event->title }}
                                    </a>
                                </td>
                                <td>{{ $event->start_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $event->end_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($event->available)
                                        <span class="badge bg-success">Público</span>
                                    @else
                                        <span class="badge bg-danger">Oculto</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('events.edit', $event->uid) }}" class="btn btn-sm btn-info" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('events.destroy', $event->uid) }}" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay eventos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
