<!-- Sidebar Start - Menu Dinámico con NavService -->

@php
    use App\Services\NavService;
    $miniItems = NavService::getMiniItems();
    $allSidebars = NavService::getAllSidebars();

    // Detectar qué sidebar debe estar activo basado en la ruta actual
    $activeSidebarId = null;
    $currentRoute = request()->route()?->getName() ?? '';

    foreach ($allSidebars as $sidebarId => $sidebar) {
        // Soportar ambas estructuras: sections (nueva) e items (legacy)
        if (isset($sidebar['sections'])) {
            // Nueva estructura con múltiples secciones
            foreach ($sidebar['sections'] as $section) {
                foreach ($section['items'] ?? [] as $item) {
                    if (request()->routeIs($item['route'] . '*')) {
                        $activeSidebarId = $sidebarId;
                        break 3;
                    }
                }
            }
        } else {
            // Estructura legacy con items directos
            foreach ($sidebar['items'] ?? [] as $item) {
                if (request()->routeIs($item['route'] . '*')) {
                    $activeSidebarId = $sidebarId;
                    break 2;
                }
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
                            @php
                                $sections = isset($sidebar['sections']) ? $sidebar['sections'] : [['title' => $sidebar['title'] ?? 'Menu', 'items' => $sidebar['items'] ?? []]];
                            @endphp

                            @forelse($sections as $section)
                                <!-- Sección de configuración con nav-small-cap -->
                                <li class="nav-small-cap">
                                    <span class="hide-menu">{{ $section['title'] }}</span>
                                </li>

                                @forelse($section['items'] as $item)
                                    @php
                                        $currentRouteName = request()->route()?->getName() ?? '';
                                        $itemRoute = $item['route'];
                                        $isActive = $currentRouteName === $itemRoute;
                                    @endphp
                                    <li class="sidebar-item">
                                        <a href="{{ route($item['route']) }}"
                                           data-current-route="{{ $currentRouteName }}"
                                           data-item-route="{{ $itemRoute }}"
                                           data-is-active="{{ $isActive ? 'true' : 'false' }}"
                                           class="sidebar-link {{ $isActive ? 'active' : '' }}">
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
                            @empty
                                <li class="sidebar-item">
                                    <span class="hide-menu text-muted">Sin secciones</span>
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
     * Sistema de Navegación - Detección automática de rutas y submenús
     */
    (function() {
        'use strict';

        /**
         * Alternar visibilidad de sidebars
         */
        function toggleSidebar(sidebarId, miniNavId) {
            const targetSidebar = document.getElementById('menu-right-' + sidebarId);
            const targetMiniNav = document.getElementById('mini-' + miniNavId);

            if (!targetSidebar || !targetMiniNav) {
                console.warn('toggleSidebar: Missing elements', { sidebarId, miniNavId });
                return;
            }

            // Ocultar todos los sidebars y quitar clase selected
            document.querySelectorAll('.sidebar-nav').forEach(sidebar => {
                sidebar.classList.add('d-none');
            });
            document.querySelectorAll('.mini-nav-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Mostrar el sidebar seleccionado con delay para permitir transiciones CSS
            setTimeout(() => {
                targetSidebar.classList.remove('d-none');
                targetMiniNav.classList.add('selected');

                // Dispatch event para reinicializar tooltips
                document.dispatchEvent(new CustomEvent('sidebarChanged', {
                    detail: { sidebarId, miniNavId }
                }));
            }, 50);

            // Guardar preferencia
            try {
                localStorage.setItem('activeSidebar', sidebarId);
                localStorage.setItem('activeMiniNav', miniNavId);
            } catch (e) {
                console.warn('localStorage not available:', e);
            }
        }

        // Exponer toggleSidebar al scope global para onclick
        window.toggleSidebar = toggleSidebar;

        /**
         * Detectar y marcar el link activo basándose en la URL actual
         */
        function detectActiveLink() {
            const currentPath = window.location.pathname;
            const currentUrl = window.location.href;
            let activeLinkFound = false;
            let activeSidebarId = null;

            // Buscar en todos los sidebars
            document.querySelectorAll('.sidebar-nav').forEach(sidebar => {
                const sidebarId = sidebar.id.replace('menu-right-', '');

                // Buscar links dentro de este sidebar
                sidebar.querySelectorAll('.sidebar-link').forEach(link => {
                    const href = link.getAttribute('href');

                    if (!href || href === '#' || href === 'javascript:void(0)') {
                        return;
                    }

                    // Comparar URLs de manera exacta
                    const linkUrl = new URL(href, window.location.origin);
                    const isActive = currentPath === linkUrl.pathname || currentUrl === href;

                    if (isActive) {
                        // Marcar este link como activo
                        link.classList.add('active');
                        activeLinkFound = true;
                        activeSidebarId = sidebarId;

                        // Si el link está dentro de un submenú, expandirlo
                        const parentSubmenu = link.closest('ul.collapse');
                        if (parentSubmenu) {
                            parentSubmenu.classList.add('show');
                            const parentToggle = parentSubmenu.previousElementSibling;
                            if (parentToggle && parentToggle.classList.contains('has-arrow')) {
                                parentToggle.setAttribute('aria-expanded', 'true');
                                parentToggle.closest('.sidebar-item')?.classList.add('open');
                            }
                        }
                    } else {
                        link.classList.remove('active');
                    }
                });
            });

            return { found: activeLinkFound, sidebarId: activeSidebarId };
        }

        /**
         * Inicialización del sidebar
         */
        function initializeSidebar() {
            // 1. Detectar link activo basándose en la URL
            const activeDetection = detectActiveLink();

            // 2. Buscar mini-nav item marcado por Blade (basado en route)
            const selectedMiniNav = document.querySelector('.mini-nav-item.selected');

            // 3. Determinar qué sidebar mostrar
            let sidebarToShow = null;
            let miniNavToSelect = null;

            if (selectedMiniNav) {
                // Blade ya detectó el sidebar correcto basándose en la ruta
                miniNavToSelect = selectedMiniNav.id.replace('mini-', '');
                const onclick = selectedMiniNav.getAttribute('onclick');
                sidebarToShow = onclick ? onclick.match(/'([^']+)'/)[1] : miniNavToSelect;
            } else if (activeDetection.found && activeDetection.sidebarId) {
                // Se encontró un link activo, usar ese sidebar
                sidebarToShow = activeDetection.sidebarId;

                // Buscar el mini-nav correspondiente
                const miniNav = document.querySelector(`[onclick*="'${sidebarToShow}'"]`);
                if (miniNav) {
                    miniNavToSelect = miniNav.id.replace('mini-', '');
                }
            } else {
                // Intentar restaurar desde localStorage
                const savedSidebar = localStorage.getItem('activeSidebar');
                const savedMiniNav = localStorage.getItem('activeMiniNav');

                if (savedSidebar && savedMiniNav) {
                    sidebarToShow = savedSidebar;
                    miniNavToSelect = savedMiniNav;
                }
            }

            // 4. Fallback: mostrar el primer sidebar
            if (!sidebarToShow || !miniNavToSelect) {
                const firstMiniNav = document.querySelector('.mini-nav-item');
                const firstSidebar = document.querySelector('[id^="menu-right-"]');

                if (firstMiniNav && firstSidebar) {
                    miniNavToSelect = firstMiniNav.id.replace('mini-', '');
                    sidebarToShow = firstSidebar.id.replace('menu-right-', '');
                }
            }

            // 5. Activar el sidebar seleccionado
            if (sidebarToShow && miniNavToSelect) {
                toggleSidebar(sidebarToShow, miniNavToSelect);
            }
        }

        /**
         * Manejar clicks en submenús (has-arrow)
         */
        function handleSubmenuClicks() {
            document.addEventListener('click', function(e) {
                const hasArrowLink = e.target.closest('.sidebar-link.has-arrow');

                if (hasArrowLink) {
                    e.preventDefault();

                    const parentItem = hasArrowLink.closest('.sidebar-item');
                    const submenu = hasArrowLink.nextElementSibling;

                    if (parentItem && submenu) {
                        parentItem.classList.toggle('open');

                        if (submenu.classList.contains('show')) {
                            submenu.classList.remove('show');
                            hasArrowLink.setAttribute('aria-expanded', 'false');
                        } else {
                            submenu.classList.add('show');
                            hasArrowLink.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });
        }

        /**
         * Sidebar collapse toggle (hamburger)
         */
        function initializeSidebarToggle() {
            const sidebarToggler = document.getElementById('headerCollapse');
            if (sidebarToggler) {
                sidebarToggler.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('mini-sidebar');
                });
            }
        }

        /**
         * Iniciar cuando el DOM esté listo
         */
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            handleSubmenuClicks();
            initializeSidebarToggle();
        });

    })();
</script>
@endpush
