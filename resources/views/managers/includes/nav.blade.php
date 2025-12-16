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
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                            <i class="fa fa-chart-line"></i>
                        </a>
                    </li>

                    <!-- E-commerce -->
                    <li class="mini-nav-item" id="mini-2">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="E-commerce">
                            <i class="fa fa-bag-shopping"></i>
                        </a>
                    </li>

                    <!-- Marketing -->
                    <li class="mini-nav-item" id="mini-3">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Marketing">
                            <i class="fa fa-bullhorn"></i>
                        </a>
                    </li>

                    <!-- Usuarios -->
                    <li class="mini-nav-item" id="mini-4">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Usuarios">
                            <i class="fa fa-users"></i>
                        </a>
                    </li>

                    <!-- Soporte -->
                    <li class="mini-nav-item" id="mini-5">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Soporte">
                            <i class="fa fa-headset"></i>
                        </a>
                    </li>

                    <!-- Bodega -->
                    <li class="mini-nav-item" id="mini-6">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Bodega">
                            <i class="fa fa-warehouse"></i>
                        </a>
                    </li>

                    <li><span class="sidebar-divider lg"></span></li>

                    <!-- Configuración -->
                    <li class="mini-nav-item" id="mini-7">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Configuración">
                            <i class="fa fa-sliders"></i>
                        </a>
                    </li>

                    <!-- Acceso & Permisos -->
                    @can('role:view')
                    <li class="mini-nav-item" id="mini-8">
                        <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Acceso & Permisos">
                            <i class="fa fa-shield-halved"></i>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>

            <div class="sidebarmenu">
                <!-- Dashboard Section -->
                <nav class="sidebar-nav" id="menu-right-mini-1" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-dashboard">
                        <li class="nav-small-cap">
                            <span class="hide-menu">Dashboard</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.dashboard') }}" aria-expanded="false">
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- E-commerce Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-2" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-ecommerce">
                        <!-- Suscriptiones -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Suscriptiones</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.subscribers') }}" class="sidebar-link">
                                <span class="hide-menu">Suscriptiones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.subscribers.lists') }}" class="sidebar-link">
                                <span class="hide-menu">Listas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.subscribers.conditions') }}" class="sidebar-link">
                                <span class="hide-menu">Estados</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Productos -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Productos</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.products') }}" aria-expanded="false">
                                <span class="hide-menu">Productos</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Tiendas -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Tiendas</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.shops') }}" aria-expanded="false">
                                <span class="hide-menu">Tiendas</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Marketing Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-3" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-marketing">
                        <!-- Campañas -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Campañas</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.campaigns') }}" class="sidebar-link">
                                <span class="hide-menu">Campañas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.maillists') }}" class="sidebar-link">
                                <span class="hide-menu">Listas</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Plantillas -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Plantillas</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.templates') }}" class="sidebar-link">
                                <span class="hide-menu">Plantillas campaña</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.layouts') }}" class="sidebar-link">
                                <span class="hide-menu">Plantilla correos</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Usuarios Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-4" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-usuarios">
                        <li class="nav-small-cap">
                            <span class="hide-menu">Usuarios</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.users') }}" aria-expanded="false">
                                <span class="hide-menu">Usuarios</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Soporte Section (Conversations, Tickets & FAQ) -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-5" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-soporte">
                        <!-- Conversaciones -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Conversaciones</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.conversations.show', 1) }}" class="sidebar-link">
                                <span class="hide-menu">Conversaciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.helpcenter.index') }}" class="sidebar-link">
                                <span class="hide-menu">Centro de Ayuda</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Helpdesk - Tickets Avanzado -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Helpdesk Tickets</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.tickets.index') }}" class="sidebar-link">
                                <span class="hide-menu">Gestión de Tickets</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.customers.index') }}" class="sidebar-link">
                                <span class="hide-menu">Clientes</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Bodega Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-6" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-bodega">
                        <!-- Gestión -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Gestión</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.warehouse.index') }}" class="sidebar-link">
                                <span class="hide-menu">Pisos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.warehouse.styles') }}" class="sidebar-link">
                                <span class="hide-menu">Estilos de ubicaciones</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Configuración Section -->
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-7" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-config">
                        <!-- General -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">General</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings') }}" class="sidebar-link">
                                <span class="hide-menu">Principal</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.categories') }}" class="sidebar-link">
                                <span class="hide-menu">Categorías</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.search.index') }}" class="sidebar-link">
                                <span class="hide-menu">Búsqueda</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.localization.index') }}" class="sidebar-link">
                                <span class="hide-menu">Localización</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.translations.index') }}" class="sidebar-link">
                                <span class="hide-menu">Traducciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.uploading.index') }}" class="sidebar-link">
                                <span class="hide-menu">Carga de archivos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.media.index') }}" class="sidebar-link">
                                <span class="hide-menu">Gestor de Medios</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Helpdesk -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Helpdesk</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.livechat') }}" class="sidebar-link">
                                <span class="hide-menu">Live chat</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.ai') }}" class="sidebar-link">
                                <span class="hide-menu">Inteligencia artificial</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.helpcenter.categories') }}" class="sidebar-link">
                                <span class="hide-menu">Centro de ayuda</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.campaigns.index') }}" class="sidebar-link">
                                <span class="hide-menu">Campañas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.uploading') }}" class="sidebar-link">
                                <span class="hide-menu">Carga de archivos</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Equipo y Clientes -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Equipo y clientes</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.team.members') }}" class="sidebar-link">
                                <span class="hide-menu">Miembros del equipo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.team.groups') }}" class="sidebar-link">
                                <span class="hide-menu">Grupos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.customers') }}" class="sidebar-link">
                                <span class="hide-menu">Clientes</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Personalización - Conversaciones -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Conversaciones</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.attributes.index') }}" class="sidebar-link">
                                <span class="hide-menu">Atributos personalizados</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.statuses.index') }}" class="sidebar-link">
                                <span class="hide-menu">Estados de conversación</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.tags.index') }}" class="sidebar-link">
                                <span class="hide-menu">Tags de conversación</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.views.index') }}" class="sidebar-link">
                                <span class="hide-menu">Vistas guardadas</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Personalización - Tickets -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Tickets</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets') }}" class="sidebar-link">
                                <span class="hide-menu">Configuración general</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.categories.index') }}" class="sidebar-link">
                                <span class="hide-menu">Categorías</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.groups.index') }}" class="sidebar-link">
                                <span class="hide-menu">Grupos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.canned-replies.index') }}" class="sidebar-link">
                                <span class="hide-menu">Respuestas enlatadas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.statuses.index') }}" class="sidebar-link">
                                <span class="hide-menu">Estados</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.sla-policies.index') }}" class="sidebar-link">
                                <span class="hide-menu">Políticas SLA</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.settings.tickets.views.index') }}" class="sidebar-link">
                                <span class="hide-menu">Vistas</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Comentarios y Notas de Ticket -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Comentarios & Notas</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.tickets.comments.index', '#') }}" class="sidebar-link">
                                <span class="hide-menu">Configurar comentarios</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.helpdesk.tickets.notes.index', '#') }}" class="sidebar-link">
                                <span class="hide-menu">Configurar notas</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Integraciones -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Integraciones</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.documents.configurations') }}" class="sidebar-link">
                                <span class="hide-menu">Documentos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.erp.index') }}" class="sidebar-link">
                                <span class="hide-menu">ERP</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.prestashop.index') }}" class="sidebar-link">
                                <span class="hide-menu">Prestashop</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.email.index') }}" class="sidebar-link">
                                <span class="hide-menu">Email/SMTP</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.mailers.templates.index') }}" class="sidebar-link">
                                <span class="hide-menu">Plantillas de correo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.mailers.components.index') }}" class="sidebar-link">
                                <span class="hide-menu">Componentes de correo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.mailers.variables.index') }}" class="sidebar-link">
                                <span class="hide-menu">Variables de correo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.mailers.endpoints.index') }}" class="sidebar-link">
                                <span class="hide-menu">Email Endpoints</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Base de datos -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Base de datos</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.database.index') }}" class="sidebar-link">
                                <span class="hide-menu">Base de datos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.database.cleanup.index') }}" class="sidebar-link">
                                <span class="hide-menu">Limpieza</span>
                            </a>
                        </li>

                        <li><span class="sidebar-divider"></span></li>

                        <!-- Sistema -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Sistema</span>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.system.index') }}" class="sidebar-link">
                                <span class="hide-menu">Colas y WebSockets</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.maintenance') }}" class="sidebar-link">
                                <span class="hide-menu">Mantenimiento</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.backups.index') }}" class="sidebar-link">
                                <span class="hide-menu">Backups</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.supervisor.index') }}" class="sidebar-link">
                                <span class="hide-menu">Supervisor</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.system.info.index') }}" class="sidebar-link">
                                <span class="hide-menu">Información</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('manager.settings.system.access.index') }}" class="sidebar-link">
                                <span class="hide-menu">Acceso</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Acceso & Permisos Section -->
                @can('role:view')
                <nav class="sidebar-nav scroll-sidebar d-none" id="menu-right-mini-8" data-simplebar="init">
                    <ul class="sidebar-menu" id="sidebarnav-access">
                        <!-- Control de Acceso -->
                        <li class="nav-small-cap">
                            <span class="hide-menu">Control de Acceso</span>
                        </li>
                        @can('role:view')
                        <li class="sidebar-item">
                            <a href="{{ route('manager.roles') }}" class="sidebar-link">
                                <span class="hide-menu">Roles</span>
                            </a>
                        </li>
                        @endcan

                        @can('role:create')
                        <li class="sidebar-item">
                            <a href="{{ route('manager.roles.create') }}" class="sidebar-link">
                                <span class="hide-menu">Crear Rol</span>
                            </a>
                        </li>
                        @endcan

                        <li class="sidebar-item">
                            <a href="{{ route('manager.permissions') }}" class="sidebar-link">
                                <span class="hide-menu">Permisos</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                @endcan
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar End -->
