<!-- Sidebar Start - Menu Dinámico con NavService -->

@php
    use App\Services\NavService;
    $miniItems = NavService::getMiniItems();
    $allSidebars = NavService::getAllSidebars();

    // Detectar qué sidebar debe estar activo basado en la ruta actual
    $activeSidebarId = null;
    $currentRoute = request()->route()?->getName() ?? '';

    foreach ($allSidebars as $sidebarId => $sidebar) {
        foreach ($sidebar['items'] as $item) {
            if (request()->routeIs($item['route'] . '*')) {
                $activeSidebarId = $sidebarId;
                break 2;
            }
        }
    }

    // Si no se encontró, usar el primer sidebar
    if (!$activeSidebarId && count($allSidebars) > 0) {
        $activeSidebarId = array_key_first($allSidebars);
    }
@endphp

<aside class="side-mini-panel with-vertical">
    <!-- Vertical Layout Sidebar -->
    <div class="iconbar">
        <div>
            <!-- Mini Navigation (Iconos pequeños a la izquierda) -->
            <div class="mini-nav">
                <div class="brand-logo d-flex align-items-center justify-content-center">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="fa fa-ellipsis"></i>
                    </a>
                </div>
                <ul class="mini-nav-ul simplebar-scrollable-y" data-simplebar="init">
                    @forelse($miniItems as $miniItem)
                        @php
                            $isActive = $activeSidebarId === $miniItem['sidebar_id'];
                        @endphp
                        <li class="mini-nav-item {{ $isActive ? 'selected' : '' }}"
                            id="mini-{{ $miniItem['id'] }}"
                            onclick="toggleSidebar('{{ $miniItem['sidebar_id'] }}', '{{ $miniItem['id'] }}')">
                            <a href="javascript:void(0)"
                               data-bs-toggle="tooltip"
                               data-bs-custom-class="custom-tooltip"
                               data-bs-placement="right"
                               data-bs-title="{{ $miniItem['tooltip'] }}">
                                <i class="fa {{ $miniItem['icon'] }}"></i>
                            </a>
                        </li>
                    @empty
                        <li class="text-muted text-center p-3">
                            <small>No hay menús registrados</small>
                        </li>
                    @endforelse
                </ul>
            </div>

            <!-- Sidebars (Menús desplegables) -->
            <div class="sidebarmenu">
                @forelse($allSidebars as $sidebarId => $sidebar)
                    @php
                        $sidebarIsActive = $activeSidebarId === $sidebarId;
                    @endphp
                    <nav class="sidebar-nav scroll-sidebar {{ $sidebarIsActive ? '' : 'd-none' }}"
                         id="menu-right-{{ $sidebarId }}"
                         data-simplebar="init">
                        <ul class="sidebar-menu" id="sidebarnav-{{ $sidebarId }}">
                            <li class="nav-small-cap">
                                <span class="hide-menu">{{ $sidebar['title'] }}</span>
                            </li>

                            @forelse($sidebar['items'] as $item)
                                <li class="sidebar-item">
                                    <a href="{{ route($item['route']) }}"
                                       class="sidebar-link {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
                                        @if(!empty($item['icon']))
                                            <i class="fa {{ $item['icon'] }} me-2"></i>
                                        @endif
                                        <span class="hide-menu">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="sidebar-item">
                                    <span class="hide-menu text-muted">Sin opciones</span>
                                </li>
                            @endforelse
                        </ul>
                    </nav>
                @empty
                    <!-- Sin sidebars registrados -->
                @endforelse
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar End -->

@push('scripts')
<script>
    /**
     * Alternar visibilidad de sidebars y guardar preferencia del usuario
     * @param {string} sidebarId - ID del sidebar a mostrar (sin prefijo 'menu-right-')
     * @param {string} miniNavId - ID del mini-nav item (sin prefijo 'mini-')
     */
    function toggleSidebar(sidebarId, miniNavId) {
        // Obtener elementos del DOM
        const targetSidebar = document.getElementById('menu-right-' + sidebarId);
        const targetMiniNav = document.getElementById('mini-' + miniNavId);

        if (!targetSidebar || !targetMiniNav) {
            console.warn('toggleSidebar: Missing elements', { sidebarId, miniNavId, targetSidebar, targetMiniNav });
            return;
        }

        // Ocultar todos los sidebars y quitar clase selected de todos los mini-nav items
        document.querySelectorAll('.sidebar-nav').forEach(sidebar => {
            sidebar.classList.add('d-none');
        });
        document.querySelectorAll('.mini-nav-item').forEach(item => {
            item.classList.remove('selected');
        });

        // Mostrar el sidebar seleccionado y marcar el mini-nav como activo
        targetSidebar.classList.remove('d-none');
        targetMiniNav.classList.add('selected');

        // Guardar preferencia del usuario en localStorage
        try {
            localStorage.setItem('activeSidebar', sidebarId);
            localStorage.setItem('activeMiniNav', miniNavId);
        } catch (e) {
            console.warn('localStorage not available:', e);
        }
    }

    /**
     * Inicializar el sidebar activo cuando se carga la página
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Intentar restaurar desde localStorage (usuarios que regresan)
        const savedSidebar = localStorage.getItem('activeSidebar');
        const savedMiniNav = localStorage.getItem('activeMiniNav');

        if (savedSidebar && savedMiniNav) {
            toggleSidebar(savedSidebar, savedMiniNav);
            return;
        }

        // Buscar el mini-nav item que tiene la clase 'selected' (establecido por Blade)
        const selectedMiniNav = document.querySelector('.mini-nav-item.selected');
        if (selectedMiniNav) {
            const miniNavId = selectedMiniNav.id.replace('mini-', '');
            toggleSidebar(miniNavId, miniNavId);
            return;
        }

        // Fallback: mostrar el primer sidebar si no hay nada seleccionado
        const firstMiniNav = document.querySelector('.mini-nav-item');
        const firstSidebar = document.querySelector('[id^="menu-right-"]');
        if (firstMiniNav && firstSidebar) {
            const firstMiniNavId = firstMiniNav.id.replace('mini-', '');
            const firstSidebarId = firstSidebar.id.replace('menu-right-', '');
            toggleSidebar(firstSidebarId, firstMiniNavId);
        }
    });
</script>
@endpush
