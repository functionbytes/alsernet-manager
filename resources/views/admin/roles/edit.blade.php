@extends('layouts.admin')

@section('title', 'Editar Roles - ' . $user->email)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800">← Volver</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Asignar Roles a {{ $user->email }}</h1>

        <div class="mb-6 p-4 bg-gray-50 rounded">
            <h3 class="font-semibold mb-2">📋 Información del Usuario</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Nombre:</strong> {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}</p>
            <p><strong>ID:</strong> {{ $user->id }}</p>
        </div>

        <form action="{{ route('admin.roles.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Seleccionar Roles</label>
                
                <div class="space-y-3">
                    @foreach($roles as $role)
                        <label class="flex items-center p-3 border border-gray-200 rounded hover:bg-gray-50">
                            <input type="checkbox" 
                                   name="roles[]" 
                                   value="{{ $role->id }}"
                                   {{ in_array($role->id, $userRoles) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600">
                            <div class="ml-3">
                                <span class="font-medium text-gray-900">{{ $role->name }}</span>
                                @if($role->label)
                                    <span class="text-sm text-gray-600">({{ $role->label }})</span>
                                @endif
                                @if($role->description)
                                    <p class="text-sm text-gray-500">{{ $role->description }}</p>
                                @endif

                                {{-- Show which profiles this role can access --}}
                                <div class="mt-2 text-xs text-gray-600">
                                    <strong>Acceso a:</strong>
                                    @php
                                        $profilesForRole = [];
                                        foreach($roleMappings as $profile => $rolesInProfile) {
                                            if(in_array($role->name, $rolesInProfile)) {
                                                $profilesForRole[] = $profile;
                                            }
                                        }
                                    @endphp
                                    @if($profilesForRole)
                                        @foreach($profilesForRole as $profile)
                                            <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded mr-1">
                                                {{ $profile }} → {{ $profileRoutes[$profile] ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-red-600">No tiene acceso a ningún perfil</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('roles')
                    <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                    💾 Guardar Cambios
                </button>
                <a href="{{ route('admin.roles.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
