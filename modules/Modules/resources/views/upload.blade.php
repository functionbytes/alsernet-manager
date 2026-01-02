@extends('layouts.theme')

@section('title', 'Instalar nuevo módulo')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-download me-2"></i>Instalar nuevo módulo
        </h1>
        <a href="{{ route('modules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    {{-- Alert Messages --}}
    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cargar archivo del módulo</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('modules.install') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="module_file" class="form-label">
                        <i class="fas fa-file-archive me-2"></i>Seleccionar archivo ZIP del módulo
                    </label>
                    <input type="file" class="form-control @error('module_file') is-invalid @enderror"
                           id="module_file" name="module_file" accept=".zip" required>
                    <small class="form-text text-muted d-block mt-2">
                        Por favor, seleccione un archivo ZIP que contenga el módulo de Laravel.
                        El archivo debe incluir un archivo <code>module.json</code> en su raíz.
                    </small>
                    @error('module_file')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-info-circle me-2"></i>Estructura esperada del módulo
                        </h6>
                        <p class="small text-muted mb-2">El archivo ZIP debe contener la siguiente estructura:</p>
                        <pre class="bg-white p-3 rounded" style="font-size: 0.85rem;"><code>ModuleName/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   └── web.php
├── module.json
└── composer.json (opcional)</code></pre>
                    </div>
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-file-code me-2"></i>Contenido mínimo de module.json
                        </h6>
                        <pre class="bg-white p-3 rounded" style="font-size: 0.85rem;"><code>{
    "name": "MyModule",
    "alias": "mymodule",
    "description": "Descripción del módulo",
    "version": "1.0.0",
    "providers": [
        "Modules\\MyModule\\Providers\\MyModuleServiceProvider"
    ]
}</code></pre>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-upload me-2"></i>Instalar módulo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Instructions Section --}}
    <div class="card mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-question-circle me-2"></i>Instrucciones para crear un módulo
            </h6>
        </div>
        <div class="card-body">
            <h6 class="mb-3">1. Crear la estructura del módulo</h6>
            <pre class="bg-light p-3 rounded mb-3"><code>php artisan module:make YourModuleName</code></pre>

            <h6 class="mb-3">2. Desarrollar el módulo</h6>
            <p class="text-muted">Agregue sus controllers, models, views y routes dentro de la carpeta del módulo.</p>

            <h6 class="mb-3">3. Crear un archivo ZIP</h6>
            <p class="text-muted">Comprima la carpeta de su módulo en un archivo ZIP, asegurándose de que <code>module.json</code> esté en la raíz del ZIP.</p>

            <h6 class="mb-3">4. Subir el archivo</h6>
            <p class="text-muted">Use este formulario para subir el archivo ZIP y instalar el módulo automáticamente.</p>

            <div class="alert alert-info mt-3">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Consejo:</strong> Una vez instalado el módulo, puede habilitarlo, deshabilitarlo o desinstalarlo
                desde la página de administración de módulos.
            </div>
        </div>
    </div>

</div>
@endsection
