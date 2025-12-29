@extends('layouts.managers')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Crear evento</h5>
        </div>
        <div class="card-body">
            <form id="formEvent" method="POST" action="{{ route('manager.events.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="title" class="form-label">Título del evento *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Fecha de inicio *</label>
                        <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" id="start_at" name="start_at" required>
                        @error('start_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">Fecha de finalización *</label>
                        <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" id="end_at" name="end_at" required>
                        @error('end_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="color_flag" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color_flag" name="color_flag" value="#90bb13">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="available" class="form-label">Visible</label>
                        <select class="form-select" id="available" name="available">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="featured" class="form-label">Destacado</label>
                        <select class="form-select" id="featured" name="featured">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amazing" class="form-label">Oferta especial</label>
                        <select class="form-select" id="amazing" name="amazing">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="iva" class="form-label">IVA (%)</label>
                        <input type="number" class="form-control" id="iva" name="iva" step="0.01" min="0" max="100">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear evento
                    </button>
                    <a href="{{ route('manager.events') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formEvent').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = '{{ route("manager.events") }}';
                }
            },
            error: function(xhr) {
                alert('Error al crear el evento');
            }
        });
    });
});
</script>
@endsection
