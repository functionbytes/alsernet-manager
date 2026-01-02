{{--
    Componente de navegación lateral dinámico
    Renderiza mini-nav items y sidebars registrados en AdminMenuService
--}}

<div class="admin-sidebar-navigation">
    <!-- Mini Nav Items (Navegación con iconos a la izquierda) -->
    <div class="mini-nav-container">
        <ul class="list-unstyled mini-nav">
            @forelse($adminMenuService::getMiniItems() as $item)
                <li class="mini-nav-item {{ isset($activeMiniNav) && $activeMiniNav === $item['id'] ? 'selected' : '' }}"
                    id="mini-{{ $item['id'] }}">
                    <a href="javascript:void(0)"
                       data-bs-toggle="tooltip"
                       data-bs-custom-class="custom-tooltip"
                       data-bs-placement="right"
                       data-bs-title="{{ $item['tooltip'] }}"
                       onclick="toggleSidebar('{{ $item['sidebar_id'] }}', '{{ $item['id'] }}')">
                        <i class="fa {{ $item['icon'] }}"></i>
                    </a>
                </li>
            @empty
                {{-- Sin items registrados --}}
            @endforelse
        </ul>
    </div>

    <!-- Sidebars (Menús desplegables) -->
    <div class="sidebars-container">
        @foreach($adminMenuService::getAllSidebars() as $sidebarId => $sidebar)
            <nav class="sidebar-nav scroll-sidebar d-none"
                 id="menu-right-{{ $sidebarId }}"
                 data-simplebar="init">
                <ul class="sidebar-menu" id="sidebarnav-{{ $sidebarId }}">
                    <li class="nav-small-cap">
                        <span class="hide-menu">{{ $sidebar['title'] }}</span>
                    </li>

                    @foreach($sidebar['items'] as $item)
                        <li class="sidebar-item">
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                @if(!empty($item['icon']))
                                    <i class="fa {{ $item['icon'] }} me-2"></i>
                                @endif
                                <span class="hide-menu">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    /**
     * Alternar visibilidad de sidebars
     */
    function toggleSidebar(sidebarId, miniNavId) {
        const sidebar = document.getElementById('menu-right-' + sidebarId);
        const miniNav = document.getElementById('mini-' + miniNavId);

        // Obtener todos los sidebars y mini-nav items
        const allSidebars = document.querySelectorAll('.sidebar-nav');
        const allMiniItems = document.querySelectorAll('.mini-nav-item');

        // Ocultar todos los sidebars y quitar clase selected
        allSidebars.forEach(s => s.classList.add('d-none'));
        allMiniItems.forEach(m => m.classList.remove('selected'));

        // Mostrar el sidebar seleccionado y agregar clase selected al mini-nav
        if (sidebar) {
            sidebar.classList.remove('d-none');
        }
        if (miniNav) {
            miniNav.classList.add('selected');
        }

        // Guardar preferencia en localStorage
        localStorage.setItem('activeSidebar', sidebarId);
        localStorage.setItem('activeMiniNav', miniNavId);
    }

    // Restaurar sidebar abierto al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const savedSidebar = localStorage.getItem('activeSidebar');
        const savedMiniNav = localStorage.getItem('activeMiniNav');

        if (savedSidebar && savedMiniNav) {
            toggleSidebar(savedSidebar, savedMiniNav);
        }
    });

    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

<style>
    .admin-sidebar-navigation {
        display: flex;
        height: 100vh;
    }

    .mini-nav-container {
        flex: 0 0 60px;
        background: #f8f9fa;
        border-right: 1px solid #ddd;
        padding: 1rem 0;
        overflow-y: auto;
    }

    .mini-nav {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mini-nav-item {
        text-align: center;
    }

    .mini-nav-item a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 6px;
        color: #666;
        transition: all 0.3s ease;
    }

    .mini-nav-item a:hover {
        background: #e9ecef;
        color: #333;
    }

    .mini-nav-item.selected a {
        background: #90bb13;
        color: white;
    }

    .mini-nav-item i {
        font-size: 1.25rem;
    }

    .sidebars-container {
        flex: 1;
        overflow: hidden;
    }

    .sidebar-nav {
        height: 100%;
        background: white;
        border-right: 1px solid #ddd;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-small-cap {
        padding: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #999;
        border-bottom: 1px solid #f0f0f0;
    }

    .sidebar-item {
        border-bottom: 1px solid #f9f9f9;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: #666;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .sidebar-link:hover {
        background: #f8f9fa;
        color: #333;
        padding-left: 2rem;
    }

    .sidebar-link.active {
        background: #f0f7ff;
        color: #90bb13;
        font-weight: 600;
        border-left: 3px solid #90bb13;
        padding-left: calc(1.5rem - 3px);
    }

    .hide-menu {
        margin-left: 0.5rem;
    }
</style>
