@extends('layouts.theme')

@section('content')

    <div class="widget-content searchable-container list">

        @include('theme.components.alerts')

        {{-- Main Card --}}
        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @php
                            $roleName = $user->getRoleNames()->first() ?? 'Sin rol';
                            $badgeClass = match($roleName) {
                                'super-admin' => 'bg-light-danger text-danger',
                                'manager' => 'bg-light-success text-success',
                                'customer' => 'bg-light-info text-info',
                                default => 'bg-light-secondary text-dark'
                            };
                        @endphp
                        <h5 class="mb-1 fw-bold">Mi perfil</h5>
                        <p class="small mb-0 text-muted">Gestiona tu cuenta, seguridad y preferencias</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('core.dashboard') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-primary h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Nombre completo</h6>
                                        <h5 style="font-weight: 700;">{{ $user->firstname }}</h5>
                                        <small class="text-muted">{{ $user->lastname }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-success h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Rol asignado</h6>
                                        <h5 class="text-success" style="font-weight: 700;">{{ Str::title(str_replace('-', ' ', $roleName)) }}</h5>
                                        <small class="text-muted">Permisos del usuario</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-{{ $user->available ? 'success' : 'danger' }} h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Estado</h6>
                                        <h5 class="text-{{ $user->available ? 'success' : 'danger' }}" style="font-weight: 700;">{{ $user->available ? 'Activo' : 'Inactivo' }}</h5>
                                        <small class="text-muted">{{ $user->available ? 'Cuenta activa' : 'Cuenta desactivada' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-{{ $user->email_verified_at ? 'success' : 'warning' }} h-100">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-2">Verificación</h6>
                                        <h5 class="text-{{ $user->email_verified_at ? 'success' : 'warning' }}" style="font-weight: 700;">{{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}</h5>
                                        <small class="text-muted">Estado del email</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Pills --}}
            <ul class="nav nav-pills user-profile-tab" id="user-profile-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'account' ? 'active' : '' }}"
                            id="account-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#account"
                            type="button"
                            role="tab"
                            aria-controls="account"
                            aria-selected="{{ $activeTab === 'account' ? 'true' : 'false' }}">
                        <i class="fas fa-user me-2"></i>
                        <span class="d-none d-md-block">Cuenta</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'password' ? 'active' : '' }}"
                            id="password-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#password"
                            type="button"
                            role="tab"
                            aria-controls="password"
                            aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}">
                        <i class="fas fa-key me-2"></i>
                        <span class="d-none d-md-block">Contraseña</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'sessions' ? 'active' : '' }}"
                            id="sessions-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#sessions"
                            type="button"
                            role="tab"
                            aria-controls="sessions"
                            aria-selected="{{ $activeTab === 'sessions' ? 'true' : 'false' }}">
                        <i class="fas fa-clock me-2"></i>
                        <span class="d-none d-md-block">Sesiones</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'two-factor' ? 'active' : '' }}"
                            id="two-factor-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#two-factor"
                            type="button"
                            role="tab"
                            aria-controls="two-factor"
                            aria-selected="{{ $activeTab === 'two-factor' ? 'true' : 'false' }}">
                        <i class="fas fa-shield-halved me-2"></i>
                        <span class="d-none d-md-block">2FA</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'devices' ? 'active' : '' }}"
                            id="devices-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#devices"
                            type="button"
                            role="tab"
                            aria-controls="devices"
                            aria-selected="{{ $activeTab === 'devices' ? 'true' : 'false' }}">
                        <i class="fas fa-mobile-screen me-2"></i>
                        <span class="d-none d-md-block">Dispositivos</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 {{ $activeTab === 'activity' ? 'active' : '' }}"
                            id="activity-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#activity"
                            type="button"
                            role="tab"
                            aria-controls="activity"
                            aria-selected="{{ $activeTab === 'activity' ? 'true' : 'false' }}">
                        <i class="fas fa-history me-2"></i>
                        <span class="d-none d-md-block">Actividad</span>
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="card-body">
                <div class="tab-content" id="user-profile-content">

                    {{-- Tab: Cuenta --}}
                    <div class="tab-pane fade {{ $activeTab === 'account' ? 'show active' : '' }}" id="account" role="tabpanel" aria-labelledby="account-tab">
                        @include('auth::settings.profile.tabs.account')
                    </div>

                    {{-- Tab: Contraseña --}}
                    <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}" id="password" role="tabpanel" aria-labelledby="password-tab">
                        @include('auth::settings.profile.tabs.password')
                    </div>

                    {{-- Tab: Sesiones --}}
                    <div class="tab-pane fade {{ $activeTab === 'sessions' ? 'show active' : '' }}" id="sessions" role="tabpanel" aria-labelledby="sessions-tab">
                        @include('auth::settings.profile.tabs.sessions')
                    </div>

                    {{-- Tab: Two-Factor --}}
                    <div class="tab-pane fade {{ $activeTab === 'two-factor' ? 'show active' : '' }}" id="two-factor" role="tabpanel" aria-labelledby="two-factor-tab">
                        @include('auth::settings.profile.tabs.two-factor')
                    </div>

                    {{-- Tab: Dispositivos --}}
                    <div class="tab-pane fade {{ $activeTab === 'devices' ? 'show active' : '' }}" id="devices" role="tabpanel" aria-labelledby="devices-tab">
                        @include('auth::settings.profile.tabs.devices')
                    </div>

                    {{-- Tab: Actividad --}}
                    <div class="tab-pane fade {{ $activeTab === 'activity' ? 'show active' : '' }}" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        @include('auth::settings.profile.tabs.activity')
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Ensure Bootstrap pills work correctly
    const tabs = document.querySelectorAll('[data-bs-toggle="pill"]');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
});
</script>
@endpush
