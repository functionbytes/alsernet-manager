<header class="topbar">
    <div class="with-vertical">



        <nav class="navbar navbar-expand-lg p-0">


            <!-- Botón Mobile Sidebar Toggle (solo visible en mobile) -->

            <a href="javascript:void(0)"
               class="nav-link nav-icon-hover-bg rounded-circle d-flex d-lg-none align-items-center justify-content-center sidebartoggler"
               id="headerCollapse"
               type="button">
                <i class="fa-duotone fa-bars fs-6"></i>
            </a>

            <!-- Navbar Toggler Button (visible on small screens) -->
            <a class="navbar-toggler p-0 border-0 nav-icon-hover-bg rounded-circle d-lg-none"
               href="javascript:void(0)"
               data-bs-toggle="collapse"
               data-bs-target="#navbarNav"
               aria-controls="navbarNav"
               aria-expanded="false"
               aria-label="Toggle navigation">
                <i class="fa-duotone fa-ellipsis"></i>
            </a>



            <div class="navbar-collapse justify-content-end " id="navbarNav">
                <div class="d-flex justify-content-end w-100">
                    <ul class="navbar-nav flex-row">

                        <!-- Notifications -->
                        @include('notification::components.notifications')

                        <!-- Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="javascript:void(0)" id="drop1" aria-expanded="false">
                                    @php
                                        $initials = strtoupper(substr(Auth::user()->firstname, 0, 1) . substr(Auth::user()->lastname, 0, 1));
                                        $colors = ['#cfcfcf', '#cfcfcf', '#cfcfcf', '#cfcfcf', '#cfcfcf'];
                                        $colorIndex = ord($initials[0]) % count($colors);
                                        $bgColor = $colors[$colorIndex];
                                    @endphp
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-light">
                                        {{ $initials }}
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
                    </ul>
                </div>
            </div>

        </nav>

    </div>
</header>

