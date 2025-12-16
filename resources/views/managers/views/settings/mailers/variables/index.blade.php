@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Gestionar Variables de Email'])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ request()->fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                            <i data-feather="search"></i>
                                        </span>
                                        <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar variable..." value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <select class="form-select select2" name="module" data-minimum-results-for-search="Infinity">
                                    <option value="">Todos los módulos</option>
                                    <option value="core" @selected(request('module') === 'core')>Core</option>
                                    <option value="documents" @selected(request('module') === 'documents')>Documentos</option>
                                    <option value="orders" @selected(request('module') === 'orders')>Pedidos</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select select2" name="category" data-minimum-results-for-search="Infinity">
                                    <option value="">Todas las categorías</option>
                                    <option value="system" @selected(request('category') === 'system')>Sistema</option>
                                    <option value="customer" @selected(request('category') === 'customer')>Cliente</option>
                                    <option value="order" @selected(request('category') === 'order')>Pedido</option>
                                    <option value="document" @selected(request('category') === 'document')>Documento</option>
                                    <option value="general" @selected(request('category') === 'general')>General</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('manager.settings.mailers.variables.create') }}" class="btn btn-primary">
                                    <i class="fa-duotone fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table search-table align-middle text-nowrap">
                    <thead class="header-item">
                        <tr>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Módulo</th>
                            <th>Estado</th>
                            <th>Sistema</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($variables as $variable)
                            <tr class="search-items">
                                <td>
                                    <code class="text-primary">{{ $variable->key }}</code>
                                </td>
                                <td>
                                    <span class="usr-email-addr">{{ $variable->name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-info text-info rounded-3 py-2">
                                        @switch($variable->category)
                                            @case('system')
                                                Sistema
                                                @break
                                            @case('customer')
                                                Cliente
                                                @break
                                            @case('order')
                                                Pedido
                                                @break
                                            @case('document')
                                                Documento
                                                @break
                                            @default
                                                General
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light-warning text-warning rounded-3 py-2">
                                        @switch($variable->module)
                                            @case('documents')
                                                Documentos
                                                @break
                                            @case('orders')
                                                Pedidos
                                                @break
                                            @default
                                                Core
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox"
                                            @checked($variable->is_enabled)
                                            data-variable-id="{{ $variable->id }}"
                                            data-url="{{ route('manager.settings.mailers.variables.toggle-status', $variable) }}">
                                    </div>
                                </td>
                                <td>
                                    @if ($variable->is_system)
                                        <span class="badge bg-light-danger text-danger rounded-3 py-2">
                                            <i class="fa-duotone fa-lock"></i> Sistema
                                        </span>
                                    @else
                                        <span class="badge bg-light-secondary text-secondary rounded-3 py-2">
                                            Custom
                                        </span>
                                    @endif
                                </td>
                                <td class="text-left">
                                    <div class="dropdown dropstart">
                                        <a href="#" class="text-muted" id="dropdownMenuButton{{ $variable->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $variable->id }}">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('manager.settings.mailers.variables.edit', $variable) }}">
                                                    <i class="fa-duotone fa-pen-to-square"></i> Editar
                                                </a>
                                            </li>
                                            @if (!$variable->is_system)
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-3 text-danger confirm-delete" data-href="{{ route('manager.settings.mailers.variables.destroy', $variable) }}">
                                                        <i class="fa-duotone fa-trash"></i> Eliminar
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No hay variables definidas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($variables->count())
                <div class="result-body">
                    <span>Mostrar {{ $variables->firstItem() }}-{{ $variables->lastItem() }} de {{ $variables->total() }} resultados</span>
                    <nav>
                        {{ $variables->appends(request()->input())->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-status').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const url = this.dataset.url;
                const variableId = this.dataset.variableId;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Show success message
                        console.log(data.message);
                    } else {
                        // Revert the toggle if failed
                        this.checked = !this.checked;
                        console.error(data.message);
                    }
                } catch (error) {
                    this.checked = !this.checked;
                    console.error('Error:', error);
                }
            });
        });
    </script>

@endsection
