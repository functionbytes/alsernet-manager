@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Perfil de Usuario'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    {{-- User Profile Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-4">
                                <div class="bg-light-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-3x text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="mb-1">{{ $user->firstname }} {{ $user->lastname }}</h3>
                                <p class="mb-1">
                                    @php
                                        $roleName = $user->getRoleNames()->first() ?? 'Sin rol';
                                        $badgeClass = match($roleName) {
                                            'super-admin' => 'bg-light-danger text-danger',
                                            'manager' => 'bg-light-success text-success',
                                            'customer' => 'bg-light-info text-info',
                                            default => 'bg-light-secondary text-dark'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        <i class="fas fa-shield-alt me-1"></i>{{ Str::title(str_replace('-', ' ', $roleName)) }}
                                    </span>
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('settings.users.edit', $user->uid) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-2"></i>Editar Usuario
                            </a>
                            <a href="{{ route('settings.users.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>

                    {{-- Stats Cards --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light-info border-0 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-fingerprint fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 small">{{ Str::limit($user->uid, 15) }}</h6>
                                            <small class="text-muted">UID</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-light-{{ $user->available ? 'success' : 'danger' }} border-0 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-circle-check fa-2x text-{{ $user->available ? 'success' : 'danger' }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->available ? 'Activo' : 'Inactivo' }}</h6>
                                            <small class="text-muted">Estado</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-light-warning border-0 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->created_at->format('d/m/Y') }}</h6>
                                            <small class="text-muted">Registrado</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-light-{{ $user->email_verified_at ? 'success' : 'secondary' }} border-0 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-{{ $user->email_verified_at ? 'check-circle' : 'question-circle' }} fa-2x text-{{ $user->email_verified_at ? 'success' : 'dark' }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->email_verified_at ? 'Verificado' : 'Sin verificar' }}</h6>
                                            <small class="text-muted">Email</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabbed Content --}}
    <div class="card">
        <ul class="nav nav-pills user-profile-tab border-bottom" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-5 py-3 px-4"
                    id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab"
                    aria-controls="pills-account" aria-selected="true">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="d-none d-md-block">Cuenta</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-5 py-3 px-4"
                    id="pills-details-tab" data-bs-toggle="pill" data-bs-target="#pills-details" type="button"
                    role="tab" aria-controls="pills-details" aria-selected="false">
                    <i class="fas fa-id-card me-2"></i>
                    <span class="d-none d-md-block">Detalles</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-5 py-3 px-4"
                    id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab"
                    aria-controls="pills-security" aria-selected="false">
                    <i class="fas fa-lock me-2"></i>
                    <span class="d-none d-md-block">Seguridad</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-5 py-3 px-4"
                    id="pills-activity-tab" data-bs-toggle="pill" data-bs-target="#pills-activity" type="button" role="tab"
                    aria-controls="pills-activity" aria-selected="false">
                    <i class="fas fa-history me-2"></i>
                    <span class="d-none d-md-block">Actividad</span>
                </button>
            </li>
        </ul>

        <div class="card-body">
            <div class="tab-content" id="pills-tabContent">
                {{-- Tab: Account --}}
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel"
                    aria-labelledby="pills-account-tab" tabindex="0">
                    <div class="row">
                        {{-- Change Password --}}
                        <div class="col-lg-6 mb-4">
                            <div class="card border">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">Cambiar contraseña</h6>
                                    <p class="card-subtitle mb-4 small text-muted">Actualiza tu contraseña de acceso al sistema</p>
                                    <form id="formChangePassword">
                                        @csrf
                                        <input type="hidden" name="uid" value="{{ $user->uid }}">

                                        <div class="mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <input type="password" class="form-control" name="password"
                                                   placeholder="Mínimo 8 caracteres" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Confirmar Contraseña</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                   placeholder="Confirma la contraseña" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-key me-2"></i>Actualizar Contraseña
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Account Status --}}
                        <div class="col-lg-6 mb-4">
                            <div class="card border">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">Estado de la cuenta</h6>
                                    <p class="card-subtitle mb-4 small text-muted">Información del estado actual de la cuenta</p>

                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-{{ $user->available ? 'success' : 'danger' }} rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-power-off text-{{ $user->available ? 'success' : 'danger' }} fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Estado</h6>
                                                <p class="mb-0 small text-muted">{{ $user->available ? 'Cuenta activa' : 'Cuenta inactiva' }}</p>
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $user->available ? 'success' : 'danger' }}">
                                            {{ $user->available ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-{{ $user->email_verified_at ? 'success' : 'warning' }} rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-envelope-circle-check text-{{ $user->email_verified_at ? 'success' : 'warning' }} fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Email verificado</h6>
                                                <p class="mb-0 small text-muted">
                                                    @if($user->email_verified_at)
                                                        {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                                    @else
                                                        Pendiente de verificación
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                            {{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-info rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-calendar-check text-info fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Último acceso</h6>
                                                <p class="mb-0 small text-muted">{{ $user->updated_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Details --}}
                <div class="tab-pane fade" id="pills-details" role="tabpanel"
                    aria-labelledby="pills-details-tab" tabindex="0">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border mb-0">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-4">Información completa del usuario</h6>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-muted small mb-2">INFORMACIÓN PERSONAL</h6>
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" width="150">Nombre:</td>
                                                        <td><strong>{{ $user->firstname }} {{ $user->lastname }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Email:</td>
                                                        <td><strong>{{ $user->email }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Identificación:</td>
                                                        <td>{{ $user->identification ?: 'No especificado' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Teléfono:</td>
                                                        <td>{{ $user->cellphone ?: 'No especificado' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Dirección:</td>
                                                        <td>{{ $user->address ?: 'No especificada' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-muted small mb-2">INFORMACIÓN ADICIONAL</h6>
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" width="150">Empresa:</td>
                                                        <td>{{ $user->company ?: 'No especificada' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Zona horaria:</td>
                                                        <td>{{ $user->timezone ?: 'No configurada' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Rol:</td>
                                                        <td>
                                                            <span class="badge {{ $badgeClass }}">
                                                                {{ Str::title(str_replace('-', ' ', $roleName)) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">UID:</td>
                                                        <td><code class="small">{{ $user->uid }}</code></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-12">
                                            <h6 class="text-muted small mb-2">AUDITORÍA</h6>
                                            <table class="table table-sm mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" width="150">Creado:</td>
                                                        <td>
                                                            {{ $user->created_at->format('d/m/Y H:i') }}
                                                            <span class="text-muted small">({{ $user->created_at->diffForHumans() }})</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Actualizado:</td>
                                                        <td>
                                                            {{ $user->updated_at->format('d/m/Y H:i') }}
                                                            <span class="text-muted small">({{ $user->updated_at->diffForHumans() }})</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Security --}}
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab"
                    tabindex="0">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">Configuración de seguridad</h6>
                                    <p class="text-muted small mb-4">Gestiona las opciones de seguridad de la cuenta</p>

                                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-{{ $user->email_verified_at ? 'success' : 'warning' }} rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-envelope-circle-check text-{{ $user->email_verified_at ? 'success' : 'warning' }} fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Verificación de email</h6>
                                                <p class="mb-0 small text-muted">Estado de verificación del correo electrónico</p>
                                            </div>
                                        </div>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verificado</span>
                                        @else
                                            <button class="btn btn-sm btn-primary">Verificar ahora</button>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-secondary rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-mobile-screen text-dark fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Autenticación de dos factores</h6>
                                                <p class="mb-0 small text-muted">Añade una capa adicional de seguridad</p>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary">Configurar</button>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light-secondary rounded-1 p-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-key text-dark fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Cambiar contraseña</h6>
                                                <p class="mb-0 small text-muted">Última actualización: {{ $user->updated_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <a href="#pills-account" data-bs-toggle="pill" class="btn btn-sm btn-outline-primary">Cambiar</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border bg-light">
                                <div class="card-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-shield-halved fa-3x text-primary mb-3"></i>
                                        <h6 class="fw-bold">Nivel de seguridad</h6>
                                    </div>

                                    @php
                                        $securityScore = 0;
                                        if ($user->email_verified_at) $securityScore += 50;
                                        if (strlen($user->password) > 8) $securityScore += 25;
                                        if ($user->updated_at->diffInDays() < 90) $securityScore += 25;

                                        $securityLevel = $securityScore >= 75 ? 'Alto' : ($securityScore >= 50 ? 'Medio' : 'Bajo');
                                        $securityColor = $securityScore >= 75 ? 'success' : ($securityScore >= 50 ? 'warning' : 'danger');
                                    @endphp

                                    <div class="progress mb-3" style="height: 10px;">
                                        <div class="progress-bar bg-{{ $securityColor }}" role="progressbar"
                                             style="width: {{ $securityScore }}%"
                                             aria-valuenow="{{ $securityScore }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <h5 class="mb-0 text-{{ $securityColor }}">{{ $securityLevel }}</h5>
                                        <small class="text-muted">{{ $securityScore }}% seguro</small>
                                    </div>

                                    <div class="mt-4">
                                        <small class="text-muted d-block mb-2">Mejora tu seguridad:</small>
                                        <ul class="list-unstyled small">
                                            @if(!$user->email_verified_at)
                                                <li class="mb-1"><i class="fas fa-circle-xmark text-danger me-2"></i>Verifica tu email</li>
                                            @endif
                                            <li class="mb-1"><i class="fas fa-circle-check text-success me-2"></i>Contraseña segura</li>
                                            <li class="mb-1"><i class="fas fa-circle-xmark text-danger me-2"></i>Activa 2FA</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Activity --}}
                <div class="tab-pane fade" id="pills-activity" role="tabpanel" aria-labelledby="pills-activity-tab"
                    tabindex="0">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border mb-0">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-4">Historial de actividad</h6>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        El registro de actividad del usuario se mostrará aquí cuando esté disponible.
                                    </div>

                                    <div class="timeline-widget mt-4">
                                        <div class="timeline-item d-flex position-relative pb-4">
                                            <div class="timeline-time text-muted small me-3" style="min-width: 120px;">
                                                {{ $user->updated_at->format('d M, H:i') }}
                                            </div>
                                            <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                                <div class="timeline-badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 12px; height: 12px;"></div>
                                                <div class="timeline-badge-border bg-light" style="width: 2px; height: 100%;"></div>
                                            </div>
                                            <div class="timeline-desc fs-3 text-dark ms-3 flex-grow-1">
                                                <h6 class="mb-1">Perfil actualizado</h6>
                                                <p class="mb-0 small text-muted">Se actualizó la información del usuario</p>
                                            </div>
                                        </div>

                                        <div class="timeline-item d-flex position-relative pb-4">
                                            <div class="timeline-time text-muted small me-3" style="min-width: 120px;">
                                                {{ $user->created_at->format('d M, H:i') }}
                                            </div>
                                            <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                                <div class="timeline-badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 12px; height: 12px;"></div>
                                            </div>
                                            <div class="timeline-desc fs-3 text-dark ms-3 flex-grow-1">
                                                <h6 class="mb-1">Usuario creado</h6>
                                                <p class="mb-0 small text-muted">Cuenta registrada en el sistema</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Change Password Form
    $("#formChangePassword").validate({
        rules: {
            password: {
                required: true,
                minlength: 8,
            },
            password_confirmation: {
                required: true,
                equalTo: "input[name='password']"
            }
        },
        messages: {
            password: {
                required: "La contraseña es obligatoria.",
                minlength: "Debe contener al menos 8 caracteres."
            },
            password_confirmation: {
                required: "Debes confirmar la contraseña.",
                equalTo: "Las contraseñas no coinciden."
            }
        },
        submitHandler: function(form) {
            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...');

            $.ajax({
                url: "{{ route('settings.users.update') }}",
                type: "POST",
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Contraseña actualizada correctamente');
                    $(form)[0].reset();
                    submitBtn.prop('disabled', false).html('<i class="fas fa-key me-2"></i>Actualizar Contraseña');
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-key me-2"></i>Actualizar Contraseña');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al actualizar la contraseña. Intente nuevamente.');
                    }

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    }
                }
            });
        }
    });
});
</script>
@endpush
