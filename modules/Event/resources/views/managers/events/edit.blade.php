@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar evento: {{ $event->title }}</h5>
        </div>
        <div class="card-body">
            <form id="formEvent" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="title" class="form-label">Título del evento *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ $event->title }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Fecha de inicio *</label>
                        <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" id="start_at" name="start_at" value="{{ $event->start_at?->format('Y-m-d\TH:i') }}" required>
                        @error('start_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">Fecha de finalización *</label>
                        <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" id="end_at" name="end_at" value="{{ $event->end_at?->format('Y-m-d\TH:i') }}" required>
                        @error('end_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="color_flag" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color_flag" name="color_flag" value="{{ $event->color_flag ?? '#90bb13' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="available" class="form-label">Visible</label>
                        <select class="form-select" id="available" name="available">
                            <option value="1" @selected($event->available == 1)>Sí</option>
                            <option value="0" @selected($event->available == 0)>No</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="featured" class="form-label">Destacado</label>
                        <select class="form-select" id="featured" name="featured">
                            <option value="0" @selected($event->featured == 0)>No</option>
                            <option value="1" @selected($event->featured == 1)>Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amazing" class="form-label">Oferta especial</label>
                        <select class="form-select" id="amazing" name="amazing">
                            <option value="0" @selected($event->amazing == 0)>No</option>
                            <option value="1" @selected($event->amazing == 1)>Sí</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="iva" class="form-label">IVA (%)</label>
                        <input type="number" class="form-control" id="iva" name="iva" step="0.01" min="0" max="100" value="{{ $event->iva }}">
                    </div>
                </div>

                <input type="hidden" name="uid" value="{{ $event->uid }}">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar evento
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
            url: '{{ route("manager.events.update") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = '{{ route("manager.events") }}';
                }
            },
            error: function(xhr) {
                alert('Error al actualizar el evento');
            }
        });
    });
});
</script>
@endsection
