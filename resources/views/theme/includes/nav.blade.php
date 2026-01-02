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

@push('styles')
<style>
    /* Asegurar que d-none funcione correctamente para sidebars */
    .sidebarmenu {
        display: block !important;
    }

    .sidebarmenu .sidebar-nav {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        width: auto !important;
        max-width: none !important;
        overflow: visible !important;
    }

    .sidebarmenu .sidebar-nav.d-none {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        opacity: 0 !important;
    }

    /* Extra fallback */
    .sidebar-nav.d-none,
    nav.sidebar-nav.d-none {
        display: none !important;
        visibility: hidden !important;
    }
</style>
@endpush

@push('scripts')
<script>
    /**
     * Alternar visibilidad de sidebars y guardar preferencia
     */
    function toggleSidebar(sidebarId, miniNavId) {
        console.log('toggleSidebar called with:', sidebarId, miniNavId);

        const sidebar = document.getElementById('menu-right-' + sidebarId);
        const miniNav = document.getElementById('mini-' + miniNavId);

        console.log('Sidebar element:', sidebar);
        console.log('MiniNav element:', miniNav);

        // Obtener todos los sidebars y mini-nav items
        const allSidebars = document.querySelectorAll('.sidebar-nav');
        const allMiniItems = document.querySelectorAll('.mini-nav-item');

        console.log('All sidebars found:', allSidebars.length);
        console.log('All mini items found:', allMiniItems.length);

        // Ocultar todos los sidebars y quitar clase selected
        allSidebars.forEach(s => {
            s.classList.add('d-none');
            console.log('Hiding sidebar:', s.id);
        });
        allMiniItems.forEach(m => m.classList.remove('selected'));

        // Mostrar el sidebar seleccionado y agregar clase selected al mini-nav
        if (sidebar) {
            sidebar.classList.remove('d-none');
            console.log('Showing sidebar:', sidebar.id);
        } else {
            console.error('Sidebar not found: menu-right-' + sidebarId);
        }

        if (miniNav) {
            miniNav.classList.add('selected');
            console.log('Added selected to mini-nav:', miniNav.id);
        } else {
            console.error('MiniNav not found: mini-' + miniNavId);
        }

        // Guardar preferencia en localStorage
        localStorage.setItem('activeSidebar', sidebarId);
        localStorage.setItem('activeMiniNav', miniNavId);
    }

    // Restaurar sidebar abierto al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const savedSidebar = localStorage.getItem('activeSidebar');
        const savedMiniNav = localStorage.getItem('activeMiniNav');

        console.log('Page loaded. Saved sidebar:', savedSidebar, 'Saved miniNav:', savedMiniNav);

        if (savedSidebar && savedMiniNav) {
            // Restaurar desde localStorage (returning user)
            toggleSidebar(savedSidebar, savedMiniNav);
        } else {
            // First-time visitor: find the currently selected mini-nav item and show its sidebar
            const selectedMiniNav = document.querySelector('.mini-nav-item.selected');
            if (selectedMiniNav) {
                const miniNavId = selectedMiniNav.id.replace('mini-', '');
                console.log('No saved preference. Using currently selected mini-nav:', miniNavId);
                toggleSidebar(miniNavId, miniNavId);
            } else {
                // Fallback: show first sidebar if no selection found
                const firstMiniNav = document.querySelector('.mini-nav-item');
                const firstSidebar = document.querySelector('[id^="menu-right-"]');
                if (firstMiniNav && firstSidebar) {
                    const firstMiniNavId = firstMiniNav.id.replace('mini-', '');
                    const firstSidebarId = firstSidebar.id.replace('menu-right-', '');
                    console.log('No selected mini-nav found. Using first:', firstSidebarId);
                    toggleSidebar(firstSidebarId, firstMiniNavId);
                }
            }
        }
    });
</script>
@endpush
