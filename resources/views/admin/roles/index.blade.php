@extends('layouts.admin')

@section('title', 'Gestión de Roles')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Gestión de Roles de Usuarios</h1>
        <a href="{{ route('admin.roles.mappings') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            ⚙️ Configuración de Mappings
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @forelse($user->roles as $role)
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-red-500">Sin roles</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.roles.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay usuarios</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
