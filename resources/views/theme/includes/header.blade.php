<header class="topbar">
    <div class="with-vertical">
        <!-- ---------------------------------- -->
        <!-- Start Vertical Layout Header -->
        <!-- ---------------------------------- -->
        <nav class="navbar navbar-expand-lg p-0">

            <ul class="navbar-nav">
                <li class="nav-item d-flex d-xl-none">
                    <a class="nav-link nav-icon-hover-bg rounded-circle sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <div class="navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav flex-row ms-auto align-items-center">

                    @include('notification::components.notifications')

                    <li class="nav-item dropdown">
                        <a class="nav-link" href="javascript:void(0)" id="drop1" aria-expanded="false">
                            <div class="d-flex align-items-center gap-2 lh-base">
                                @php
                                    $initials = strtoupper(substr(Auth::user()->firstname, 0, 1) . substr(Auth::user()->lastname, 0, 1));
                                    $colors = ['#cfcfcf', '#cfcfcf', '#cfcfcf', '#cfcfcf', '#cfcfcf'];
                                    $colorIndex = ord($initials[0]) % count($colors);
                                    $bgColor = $colors[$colorIndex];
                                @endphp
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width: 35px; height: 35px; background-color: {{ $bgColor }}; font-size: 14px;">
                                    {{ $initials }}
                                </div>
                                <iconify-icon icon="solar:alt-arrow-down-bold" class="fs-2"></iconify-icon>
                            </div>
                        </a>
                        <div class="dropdown-menu profile-dropdown dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                            <div class="position-relative px-4 pt-3 pb-2">
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom gap-6">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width: 50px; height: 50px; background-color: {{ $bgColor }}; font-size: 20px;">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fs-3">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</h5>
                                        <span class="mb-0 d-block text-muted small">
                                            {{ Auth::user()->email }}
                                        </span>
                                    </div>
                                </div>
                                <div class="message-body">
                                    <a href="{{ route('settings.auth.profile') }}" class="p-2 dropdown-item h6 rounded-1">
                                       Configuración
                                    </a>
                                    <a href="{{ route('auth.logout') }}" class="btn btn-info px-4 waves-effect waves-light w-100">Salir</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- ------------------------------- -->
                    <!-- end profile Dropdown -->
                    <!-- ------------------------------- -->
                </ul>
            </div>
        </nav>


    </div>
</header>

