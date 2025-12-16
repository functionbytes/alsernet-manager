@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Roles'])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ request()->fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2 ">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i data-feather="search"></i></span>
                                        <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('manager.roles.create') }}" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Crear nuevo rol">
                                    <i class="fa-duotone fa-plus"></i> Crear rol
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
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Guard</th>
                        <th>Usuarios</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($roles as $key => $role)
                        <tr class="search-items">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $role->name }}
                            </td>
                            <td>
                                {{ $role->guard_name }}
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $role->users()->count() }}</span>
                            </td>

                            <td class="text-left">
                                @if(!in_array($role->name, ['super-admins', 'admins', 'customer']))
                                    <div class="dropdown dropstart">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('manager.roles.edit', $role->id) }}" class="dropdown-item edit-role"
                                                   data-role-id="{{ $role->id }}"
                                                   data-role-name="{{ $role->name }}">
                                                    <i class="fa fa-pen-to-square"></i> Editar
                                                </a>
                                            </li>

                                            <li>
                                                <a href="{{ route('manager.roles.show.permissions', $role->id) }}" class="dropdown-item manage-permissions"
                                                   data-role-id="{{ $role->id }}"
                                                   data-role-name="{{ $role->name }}">
                                                    <i class="fa fa-shield></i> Gestionar permisos
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete"
                                                   data-href="{{ route('manager.roles.destroy', $role->id) }}">
                                                    <i class="fa fa-trash"></i> Eliminar
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <span class="badge bg-warning">Rol del sistema</span>
                                @endif
                            </td>


                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
            <div class="result-body ">
                <span>Mostrar {{$roles->firstItem() }}-{{$roles->lastItem() }} de {{$roles->total() }} resultados</span>
                <nav>
                    {{$roles->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection


