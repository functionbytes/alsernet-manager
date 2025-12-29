@extends('layouts.managers')

@section('title', 'Crear Cliente - Helpdesk')

@section('content')

    @include('managers.includes.card', ['title' => 'Crear Cliente'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <form method="POST" action="{{ route('manager.helpdesk.customers.store') }}">
            @csrf

            <!-- Main Card -->
            <div class="card">
                <!-- Header Section -->
                <div class="card-header p-4 border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">Crear nuevo cliente</h5>
                            <p class="small mb-0 text-muted">Completa los datos del cliente para el sistema de helpdesk</p>
                        </div>

                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card-body border-bottom">
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Información personal</h6>
                        <p class="text-muted small mb-0">Datos básicos del cliente</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">
                                Nombre completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Ej: Juan Pérez García"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="correo@ejemplo.com"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">
                                Teléfono
                            </label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="+34 600 123 456"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="language" class="form-label fw-semibold">
                                Idioma preferido
                            </label>
                            <select id="language" name="language" class="form-select select2 @error('language') is-invalid @enderror">
                                <option value="">— Seleccionar idioma —</option>
                                <option value="es" {{ old('language') === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                                <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                                <option value="pt" {{ old('language') === 'pt' ? 'selected' : '' }}>🇵🇹 Português</option>
                                <option value="de" {{ old('language') === 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                                <option value="it" {{ old('language') === 'it' ? 'selected' : '' }}>🇮🇹 Italiano</option>
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="card-body border-bottom">
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Ubicación</h6>
                        <p class="text-muted small mb-0">Información geográfica del cliente</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="country" class="form-label fw-semibold">
                                País
                            </label>
                            <input type="text"
                                   id="country"
                                   name="country"
                                   class="form-control text-uppercase @error('country') is-invalid @enderror"
                                   placeholder="ES"
                                   value="{{ old('country') }}"
                                   maxlength="2">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Código ISO de 2 letras</small>
                        </div>

                        <div class="col-md-4">
                            <label for="state" class="form-label fw-semibold">
                                Estado/Región
                            </label>
                            <input type="text"
                                   id="state"
                                   name="state"
                                   class="form-control @error('state') is-invalid @enderror"
                                   placeholder="Madrid"
                                   value="{{ old('state') }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="city" class="form-label fw-semibold">
                                Ciudad
                            </label>
                            <input type="text"
                                   id="city"
                                   name="city"
                                   class="form-control @error('city') is-invalid @enderror"
                                   placeholder="Madrid"
                                   value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Timezone -->
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold">Configuración Regional</h6>
                        <p class="text-muted small mb-0">Zona horaria para fechas y notificaciones</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="timezone" class="form-label fw-semibold">
                                Zona Horaria
                            </label>
                            <select id="timezone" name="timezone" class="form-select select2 @error('timezone') is-invalid @enderror">
                                <option value="">— Detección automática —</option>
                                <optgroup label="Europa">
                                    <option value="Europe/Madrid" {{ old('timezone') === 'Europe/Madrid' ? 'selected' : '' }}>
                                        🇪🇸 Madrid (UTC+1/+2)
                                    </option>
                                    <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>
                                        🇬🇧 London (UTC+0/+1)
                                    </option>
                                    <option value="Europe/Paris" {{ old('timezone') === 'Europe/Paris' ? 'selected' : '' }}>
                                        🇫🇷 Paris (UTC+1/+2)
                                    </option>
                                    <option value="Europe/Berlin" {{ old('timezone') === 'Europe/Berlin' ? 'selected' : '' }}>
                                        🇩🇪 Berlin (UTC+1/+2)
                                    </option>
                                </optgroup>
                                <optgroup label="América">
                                    <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>
                                        🇺🇸 New York (UTC-5/-4)
                                    </option>
                                    <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>
                                        🇺🇸 Los Angeles (UTC-8/-7)
                                    </option>
                                    <option value="America/Mexico_City" {{ old('timezone') === 'America/Mexico_City' ? 'selected' : '' }}>
                                        🇲🇽 Ciudad de México (UTC-6)
                                    </option>
                                    <option value="America/Buenos_Aires" {{ old('timezone') === 'America/Buenos_Aires' ? 'selected' : '' }}>
                                        🇦🇷 Buenos Aires (UTC-3)
                                    </option>
                                </optgroup>
                                <optgroup label="Asia">
                                    <option value="Asia/Tokyo" {{ old('timezone') === 'Asia/Tokyo' ? 'selected' : '' }}>
                                        🇯🇵 Tokyo (UTC+9)
                                    </option>
                                    <option value="Asia/Shanghai" {{ old('timezone') === 'Asia/Shanghai' ? 'selected' : '' }}>
                                        🇨🇳 Shanghai (UTC+8)
                                    </option>
                                    <option value="Asia/Dubai" {{ old('timezone') === 'Asia/Dubai' ? 'selected' : '' }}>
                                        🇦🇪 Dubai (UTC+4)
                                    </option>
                                </optgroup>
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Zona horaria para mostrar fechas y enviar notificaciones</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            Guardar
                        </button>
                        <a href="{{ route('manager.helpdesk.customers.index') }}" class="btn btn-light w-100">
                            Cancelar
                        </a>
                </div>

            </div>
        </form>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        allowClear: true,
        placeholder: function() {
            return $(this).find('option:first').text();
        },
        language: {
            noResults: function() {
                return 'Sin resultados';
            },
            searching: function() {
                return 'Buscando...';
            }
        }
    });

    // Auto-uppercase country code
    $('#country').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
