@extends('layouts.theme')

@section('title', 'Agregar disco de almacenamiento')

@section('content')

    <div class="card">

        <form method="POST" action="{{ route('settings.storage.store') }}" id="formCreate">
            @csrf

            <div class="card-header border-bottom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Agregar disco de almacenamiento</h5>
                        <p class="mb-0 text-muted small">Configura un nuevo nombre para tu almacenamiento</p>
                    </div>
                </div>
            </div>

            <div class="card-body">

                @include('core::components.alerts')

                <div class="row">

                    {{-- Disk Name --}}
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="control-label col-form-label">
                                Nombre del disco <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Ej: documents, uploads, media"
                                   pattern="^[a-z0-9_]+$"
                                   title="Solo letras minúsculas, números y guiones bajos"
                                   required>
                            <small class="form-text text-muted">
                                Solo letras minúsculas, números y guiones bajos. Ejemplos: documents, user_uploads, media_files
                            </small>
                            @error('name')
                                <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Storage Type Selection --}}
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="control-label col-form-label mb-3">
                                Tipo de almacenamiento <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="storage_type" id="storagePublic"
                                           value="public" {{ old('storage_type') === 'public' || !old('storage_type') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="storagePublic">
                                        <strong>Público</strong>
                                        <div class="text-muted small">Accesible desde el navegador (web-accessible)</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="storage_type" id="storagePrivate"
                                           value="private" {{ old('storage_type') === 'private' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="storagePrivate">
                                        <strong>Privado</strong>
                                        <div class="text-muted small">Solo accesible por la aplicación (no público)</div>
                                    </label>
                                </div>
                            </div>
                            @error('storage_type')
                                <span class="field-validation-error d-block mt-2"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Information Box --}}
                    <div class="col-12">
                        <div class="alert alert-info border-start border-4 border-info mb-4" role="alert">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-lg"></i>
                                </div>
                                <div>
                                    <strong>¿Dónde se guardarán los archivos?</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Público:</strong> Los archivos se guardarán en <code>public/storage/{{ old('name') || 'nombre' }}</code> y serán accesibles directamente desde el navegador</li>
                                        <li><strong>Privado:</strong> Los archivos se guardarán en <code>storage/app/{{ old('name') || 'nombre' }}</code> y solo la aplicación podrá accederlos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="card-footer border-top">
                <button type="submit" class="btn btn-primary w-100 mb-1">
                    <i class="fas fa-save me-2"></i>Guardar
                </button>
                <a href="{{ route('settings.storage') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update the info box when name changes
    $('input[name="name"]').on('input', function() {
        const diskName = $(this).val() || 'nombre';
        const isPublic = $('input[name="storage_type"]:checked').val() === 'public';
        const path = isPublic
            ? `public/storage/${diskName}`
            : `storage/app/${diskName}`;
        $('.storage-path-info').text(path);
    });

    // Update path when storage type changes
    $('input[name="storage_type"]').on('change', function() {
        $('input[name="name"]').trigger('input');
    });

    // Show toastr notifications
    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
