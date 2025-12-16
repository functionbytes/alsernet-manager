@extends('layouts.admin')

@section('title', 'Configuración de Role Mappings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800">← Volver</a>
    </div>

    <h1 class="text-3xl font-bold mb-6">⚙️ Configuración de Role Mappings y Rutas</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Role Mappings Section --}}
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">📋 Role Mappings (Perfiles → Roles)</h2>
        <p class="text-gray-600 mb-4">Configura qué roles pueden acceder a cada perfil.</p>

        <div class="space-y-4">
            @foreach($roleMappings as $mapping)
                <div class="border border-gray-200 rounded p-4">
                    <h3 class="font-semibold text-lg mb-2">{{ $mapping->profile }}</h3>
                    <p class="text-gray-600 text-sm mb-3">{{ $mapping->description }}</p>

                    <form action="{{ route('admin.roles.update-mapping', $mapping) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Roles permitidos:</label>
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="roles[]" 
                                               value="{{ $role->name }}"
                                               {{ in_array($role->name, $mapping->roles ?? []) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600">
                                        <span class="ml-2 text-sm">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                            Guardar
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Profile Routes Section --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">🔗 Profile Routes (Perfiles → Dashboard)</h2>
        <p class="text-gray-600 mb-4">Configura a dónde se redirige cada perfil después del login.</p>

        <div class="space-y-4">
            @foreach($profileRoutes as $route)
                <div class="border border-gray-200 rounded p-4">
                    <h3 class="font-semibold text-lg mb-2">{{ $route->profile }}</h3>
                    <p class="text-gray-600 text-sm mb-3">{{ $route->description }}</p>

                    <form action="{{ route('admin.roles.update-route', $route) }}" method="POST" class="flex gap-3">
                        @csrf
                        @method('PUT')

                        <input type="text" 
                               name="dashboard_route" 
                               value="{{ $route->dashboard_route }}"
                               placeholder="e.g. manager.dashboard"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded">
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Guardar
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
