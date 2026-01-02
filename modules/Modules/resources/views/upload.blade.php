@extends('layouts.theme')

@section('title', 'Instalar nuevo módulo')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body">
                <div class="d-flex no-block align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Instalar nuevo módulo
                    </h5>
                    <a href="{{ route('modules.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>

                <p class="card-subtitle mb-4">
                    Carga un archivo ZIP que contenga un módulo compatible con el sistema.
                </p>

                {{-- Alert Messages --}}
                @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Upload Form Section --}}
                <form action="{{ route('modules.install') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-file-archive me-2"></i>Seleccionar archivo ZIP del módulo
                        </label>
                        <div class="custom-file-upload border-2 border-dashed rounded p-4 text-center bg-light"
                             style="cursor: pointer; transition: all 0.3s ease;"
                             id="fileDropZone">
                            <input type="file" id="module_file" name="module_file" accept=".zip" class="d-none" required>
                            <i class="fas fa-cloud-upload-alt fa-2x mb-3 text-primary d-block"></i>
                            <p class="mb-2"><strong>Arrastra un archivo ZIP aquí</strong></p>
                            <p class="text-muted small mb-0">o haz clic para seleccionar</p>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            ✓ Solo archivos ZIP | ✓ Debe contener module.json | ✓ Tamaño máximo 50MB
                        </small>
                        @error('module_file')
                            <div class="invalid-feedback d-block mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Module Structure Guide --}}
                    <hr class="my-4">

                    <div class="mb-4">
                        <h6 class="mb-3 text-uppercase fw-bold">
                            <i class="fas fa-cube me-2"></i>Estructura esperada del módulo
                        </h6>
                        <p class="text-muted small mb-2">El archivo ZIP debe contener la siguiente estructura:</p>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0 text-muted" style="font-size: 0.85rem;"><code>ModuleName/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   └── web.php
├── module.json (REQUERIDO)
├── composer.json (opcional)
└── README.md (opcional)</code></pre>
                        </div>
                    </div>

                    {{-- Module.json Template --}}
                    <div class="mb-4">
                        <h6 class="mb-3 text-uppercase fw-bold">
                            <i class="fas fa-code me-2"></i>Contenido mínimo de module.json
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0 text-muted" style="font-size: 0.85rem;"><code>{
    "name": "MyModule",
    "alias": "mymodule",
    "description": "Descripción del módulo",
    "version": "1.0.0",
    "priority": 0,
    "providers": [
        "Modules\\MyModule\\Providers\\MyModuleServiceProvider"
    ]
}</code></pre>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload me-2"></i>Instalar módulo
                        </button>
                    </div>
                </form>

                {{-- Instructions Section --}}
                <hr class="my-4">

                <div>
                    <h6 class="mb-3 text-uppercase fw-bold">
                        <i class="fas fa-question-circle me-2"></i>¿Necesitas crear un módulo?
                    </h6>
                    <p class="text-muted mb-2">Puedes crear un nuevo módulo con el siguiente comando:</p>
                    <div class="bg-light p-2 rounded mb-3">
                        <code class="text-dark">php artisan module:make YourModuleName</code>
                    </div>
                    <p class="small text-muted mb-0">
                        Para más información sobre cómo crear módulos, consulta la
                        <a href="{{ route('modules.index') }}">documentación de módulos</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // File drag and drop functionality
    const fileDropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('module_file');

    if (fileDropZone && fileInput) {
        // Click to select file
        fileDropZone.addEventListener('click', () => fileInput.click());

        // Drag and drop
        fileDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileDropZone.style.borderColor = '#0d6efd';
            fileDropZone.style.backgroundColor = '#e7f1ff';
        });

        fileDropZone.addEventListener('dragleave', () => {
            fileDropZone.style.borderColor = '#dee2e6';
            fileDropZone.style.backgroundColor = '#f8f9fa';
        });

        fileDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            fileDropZone.style.borderColor = '#dee2e6';
            fileDropZone.style.backgroundColor = '#f8f9fa';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });

        // Show selected file name
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                fileDropZone.innerHTML = `<i class="fas fa-check-circle fa-2x mb-3 text-success d-block"></i>
                                         <p class="mb-2"><strong>${fileName}</strong></p>
                                         <p class="text-muted small mb-0">Listo para instalar</p>`;
            }
        });
    }
</script>
@endpush
