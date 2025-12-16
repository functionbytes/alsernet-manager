<!-- Sidebar Start -->

<aside class="side-mini-panel with-vertical">
    <!-- Vertical Layout Sidebar -->
    <div class="iconbar">
        <div>
            <div class="mini-nav">
                <div class="brand-logo d-flex align-items-center justify-content-center">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="fa fa-ellipsis"></i>
                    </a>
                </div>
                <ul class="mini-nav-ul simplebar-scrollable-y" data-simplebar="init">
                    <!-- Dashboard -->
                    <li class="mini-nav-item" id="mini-1">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Inicio">
                            <i class="fas fa-house"></i>
                        </a>
                    </li>

                    <!-- Documentos -->
                    <li class="mini-nav-item" id="mini-2">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Documentos">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                    </li>

                    <!-- Configuración -->
                    <li class="mini-nav-item" id="mini-3">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Configuración">
                            <i class="fas fa-sliders"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebarmenu">
                <!-- Dashboard Section -->
                <nav class="sidebar-nav" id="menu-right-mini-1" data-simplebar="init">
                    <ul class="sidebar-menu" href="{{ route('home') }}">
                        <li class="nav-small-cap">
                            <span class="hide-menu">Inicio</span>
                        </li>
                    </ul>
                </nav>

                <!-- Documentos Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-2" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-documentos">
                        <li class="nav-small-cap">
                            <span class="hide-menu">Documentos</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('administrative.documents') }}" class="sidebar-link">
                                <span class="hide-menu">Todos los documentos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('administrative.documents.pending') }}" class="sidebar-link">
                                <span class="hide-menu">Documentos pendientes</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('administrative.documents.history') }}" class="sidebar-link">
                                <span class="hide-menu">Histórico</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Configuración Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-3" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-config">
                        <li class="nav-small-cap">
                            <span class="hide-menu">Configuración</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="javascript:void(0)">
                                <span class="hide-menu">Usuario</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="javascript:void(0)">
                                <span class="hide-menu">Notificaciones</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar End -->
