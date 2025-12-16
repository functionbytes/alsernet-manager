@extends('layouts.map')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mapa del Almacén</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.js@3.2.4/dist/svg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.panzoom.js@2.1.2/dist/svg.panzoom.min.js"></script>

    <style>
        /* Minimal color palette */
        :root {
            --primary: #90bb13;
            --border: #e5e7eb;
            --text: #374151;
            --text-light: #6b7280;
            --bg: #ffffff;
            --bg-light: #f9fafb;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text);
            background: var(--bg-light);
            line-height: 1.5;
        }

        /* Layout */
        .warehouse-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: var(--bg-light);
        }

        .warehouse-content {
            display: flex;
            flex: 1;
            gap: 1.5rem;
            padding: 1.5rem;
            overflow: hidden;
        }

        .content-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: 0;
        }

        /* Toolbar - Minimal */
        .content-toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .toolbar-group {
            display: flex;
            gap: 0.5rem;
        }

        .toolbar-btn {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toolbar-btn:hover {
            background: var(--bg-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .toolbar-btn.active,
        .toolbar-btn-edit.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .search-box { flex: 0 0 250px; }

        .search-box input {
            width: 100%;
            padding: 0.5rem 1rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Edit Mode Toolbar - Minimal */
        .edit-mode-toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .toolbar-mode-group { display: flex; gap: 0.5rem; }
        .toolbar-actions { display: flex; gap: 0.5rem; margin-left: auto; }

        .toolbar-mode-btn {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toolbar-mode-btn:hover { background: var(--bg-light); }
        .toolbar-mode-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        .toolbar-btn-success {
            background: #10b981;
            color: white;
            border: 1px solid #10b981;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toolbar-btn-success:hover { background: #059669; }

        /* Map Container - Clean */
        .map-container {
            flex: 1;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .stage {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
            cursor: grab;
        }

        .stage:active { cursor: grabbing; }

        /* SVG Styles - Minimal */
        #svg { width: 100%; height: 100%; }

        .grid { stroke: var(--border); stroke-width: 1; }

        .location-group { cursor: pointer; transition: opacity 0.15s; }
        .location-group:hover { opacity: 0.8; }

        .location-rect {
            stroke: var(--border);
            stroke-width: 2;
            transition: all 0.15s;
        }

        .location-text {
            fill: var(--text);
            font-size: 12px;
            font-weight: 500;
            pointer-events: none;
            user-select: none;
        }

        /* Status colors - Minimal */
        .status-empty { fill: #f3f4f6; }
        .status-available { fill: #d1fae5; }
        .status-partial { fill: #fef3c7; }
        .status-full { fill: #fecaca; }
        .status-reserved { fill: #dbeafe; }

        /* Side Panel - Minimal */
        .info-panel {
            width: 280px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-panel-section {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
        }

        .info-panel-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-panel-title i { color: var(--primary); }

        /* Floor Selector - Minimal */
        .floor-selector-panel-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .floor-btn-panel {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.625rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            text-align: left;
            transition: all 0.15s;
        }

        .floor-btn-panel:hover {
            background: var(--bg-light);
            border-color: var(--primary);
        }

        .floor-btn-panel.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Legend - Minimal */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: var(--text);
        }

        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid var(--border);
        }

        .legend-color.empty { background: #f3f4f6; }
        .legend-color.available { background: #d1fae5; }
        .legend-color.partial { background: #fef3c7; }
        .legend-color.full { background: #fecaca; }
        .legend-color.reserved { background: #dbeafe; }

        /* Shelf Info - Minimal */
        #shelfInfo {
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 6px;
            font-size: 0.875rem;
        }

        #shelfInfo p {
            margin: 0.5rem 0;
            display: flex;
            justify-content: space-between;
        }

        #shelfInfo strong { color: var(--text); }

        .btn-edit-location,
        .btn-sections {
            width: 100%;
            padding: 0.5rem;
            margin-top: 0.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.15s;
        }

        .btn-edit-location:hover,
        .btn-sections:hover {
            opacity: 0.9;
        }

        /* Loading - Minimal */
        .loading { fill: var(--text-light); }

        .spinner {
            stroke: var(--primary);
            stroke-width: 3;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Modal styles inherited from Bootstrap */
        .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }

        /* Responsive */
        @media (max-width: 1024px) {
            .warehouse-content { flex-direction: column; }
            .info-panel { width: 100%; flex-direction: row; }
            .info-panel-section { flex: 1; }
        }

        @media (max-width: 768px) {
            .info-panel { flex-direction: column; }
            .toolbar-group { flex-wrap: wrap; }
            .search-box { flex: 1 1 100%; }
        }

        /* Handle classes */
        .handle { cursor: move; }
        .resize-handle {
            width: 10px;
            height: 10px;
            fill: var(--primary);
            cursor: nwse-resize;
        }

        /* Highlighted */
        .highlighted .location-rect {
            stroke: var(--primary);
            stroke-width: 3;
            filter: drop-shadow(0 0 6px var(--primary));
        }

        /* Hidden */
        .hidden { display: none; }

        /* Alert styles - minimal */
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 6px;
        }

        .alert-info {
            color: #0369a1;
            background-color: #e0f2fe;
            border-color: #bae6fd;
        }

        .text-danger { color: #dc2626; }
        .text-muted { color: var(--text-light); }
    </style>
</head>

<body>
    <div class="warehouse-container">
        <div class="warehouse-content">
            <div class="content-main">
                <!-- Toolbar -->
                <div class="content-toolbar">
                    <div class="toolbar-group">
                        <button id="zoomIn" class="toolbar-btn" title="Zoom aumentar">
                            <i class="fas fa-magnifying-glass-plus"></i>
                        </button>
                        <button id="zoomOut" class="toolbar-btn" title="Zoom disminuir">
                            <i class="fas fa-magnifying-glass-minus"></i>
                        </button>
                        <button id="reset" class="toolbar-btn" title="Centrar vista">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button id="toggleEditMode" class="toolbar-btn toolbar-btn-edit" title="Modo edición">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="search-box" style="margin-left: auto;">
                        <input type="text" id="shelfSearch" placeholder="Buscar estante..." />
                    </div>
                </div>

                <!-- Edit Mode Toolbar -->
                <div id="editModeToolbar" class="edit-mode-toolbar" style="display: none;">
                    <div class="toolbar-mode-group">
                        <button id="modalEditBtn" class="toolbar-mode-btn active" title="Editar con formulario">
                            <i class="fas fa-edit"></i>
                            <span>Modal</span>
                        </button>
                        <button id="moveEditBtn" class="toolbar-mode-btn" title="Mover estantes">
                            <i class="fas fa-arrows-alt"></i>
                            <span>Mover</span>
                        </button>
                        <button id="resizeEditBtn" class="toolbar-mode-btn" title="Redimensionar">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>Redimensionar</span>
                        </button>
                    </div>
                    <div class="toolbar-actions">
                        <button id="createLocationBtn" class="toolbar-btn" title="Crear location">
                            <i class="fas fa-plus"></i>
                            <span>Crear</span>
                        </button>
                        <button id="autoSaveToggle" class="toolbar-btn active" title="Auto-guardar">
                            <i class="fas fa-save"></i>
                            <span id="autoSaveLabel">Auto-guardar: ON</span>
                        </button>
                        <button id="saveAllChanges" class="toolbar-btn-success" style="display: none;">
                            <i class="fas fa-save"></i>
                            <span id="unsavedCount">Guardar (0)</span>
                        </button>
                    </div>
                </div>

                <!-- Map -->
                <div class="map-container">
                    <div class="stage">
                        <svg id="svg" viewBox="0 0 800 800" width="800" height="800">
                            <defs>
                                <pattern id="smallGrid" width="50" height="50" patternUnits="userSpaceOnUse">
                                    <path d="M 50 0 L 0 0 0 50" class="grid" fill="none" />
                                </pattern>
                                <pattern id="grid" width="250" height="250" patternUnits="userSpaceOnUse">
                                    <rect width="250" height="250" fill="url(#smallGrid)" />
                                    <path d="M 250 0 L 0 0 0 250" class="grid" fill="none" stroke-width="2" />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)" />
                            <g id="world">
                                <g class="loading">
                                    <circle class="spinner" cx="400" cy="400" r="20" style="fill: none;"></circle>
                                </g>
                                <text x="50%" y="55%" text-anchor="middle" class="loading" style="font-size: 1rem;">Cargando almacén...</text>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="info-panel">
                <!-- Floor Selector -->
                <div class="info-panel-section floor-selector-panel">
                    <div class="info-panel-title">
                        <i class="fas fa-layer-group"></i>
                        Pisos
                    </div>
                    <div class="floor-selector-panel-content">
                        @foreach($floors as $floor)
                            <button id="f{{ $floor->id }}"
                                    class="floor-btn-panel @if($loop->first) active @endif"
                                    data-floor-id="{{ $floor->id }}"
                                    title="{{ $floor->description ?? $floor->name }}">
                                {{ $floor->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Legend -->
                <div class="info-panel-section">
                    <div class="info-panel-title">
                        <i class="fas fa-circle-info"></i>
                        Estados
                    </div>
                    <div>
                        <div class="legend-item">
                            <div class="legend-color empty"></div>
                            <span>Vacío</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color available"></div>
                            <span>Disponible</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color partial"></div>
                            <span>Parcial</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color full"></div>
                            <span>Completo</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color reserved"></div>
                            <span>Reservado</span>
                        </div>
                    </div>
                </div>

                <!-- Shelf Info -->
                <div class="info-panel-section" id="shelfInfoContainer" style="display: none;">
                    <div class="info-panel-title">
                        <i class="fas fa-info-circle"></i>
                        Información
                    </div>
                    <div id="shelfInfo"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Location Modal -->
    <div class="modal fade" id="createLocationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createLocationForm">
                        @csrf
                        <input type="hidden" name="floor_id" id="create_floor_id" />
                        <input type="hidden" name="x" id="create_x" value="100" />
                        <input type="hidden" name="y" id="create_y" value="100" />
                        <input type="hidden" name="width" id="create_width" value="80" />
                        <input type="hidden" name="height" id="create_height" value="40" />

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="code" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" name="capacity" value="100" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveCreateLocation">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div class="modal fade" id="editLocationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLocationForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_location_id" />
                        <input type="hidden" name="floor_id" id="edit_floor_id" />

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="code" id="edit_code" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" name="capacity" id="edit_capacity" />
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Posición X</label>
                                    <input type="number" class="form-control" name="x" id="edit_x" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Posición Y</label>
                                    <input type="number" class="form-control" name="y" id="edit_y" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Ancho</label>
                                    <input type="number" class="form-control" name="width" id="edit_width" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Alto</label>
                                    <input type="number" class="form-control" name="height" id="edit_height" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="deleteLocation">Eliminar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveEditLocation">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const config = {
            warehouseId: {{ $warehouse->id }},
            currentFloorId: {{ $floors->first()->id ?? 'null' }},
            isEditMode: false,
            editModeType: 'modal',
            autoSave: true,
            unsavedChanges: new Map(),
            locations: new Map(),
            routes: {
                locations: "{{ route('manager.warehouse.map.locations', $warehouse->uid) }}",
                update: "{{ route('manager.warehouse.map.update', $warehouse->uid) }}",
                create: "{{ route('manager.warehouse.map.create', $warehouse->uid) }}",
                delete: "{{ route('manager.warehouse.map.delete', ['warehouse_uid' => $warehouse->uid, 'location_uid' => ':id']) }}",
                sections: "{{ route('manager.warehouse.locations.sections.index', ['warehouse_uid' => $warehouse->uid, 'location_uid' => ':id']) }}"
            }
        };

        // Initialize
        let svg, world, panZoom;

        $(document).ready(function() {
            initializeSVG();
            initializeFloorSelector();
            initializeToolbar();
            initializeSearch();
            initializeModals();
            loadLocations(config.currentFloorId);
        });

        // SVG Initialization
        function initializeSVG() {
            svg = SVG().addTo('#svg').size('100%', '100%');
            world = svg.findOne('#world');

            panZoom = svg.panZoom({
                panning: true,
                zoomMin: 0.5,
                zoomMax: 3,
                zoomFactor: 0.1
            });

            $('#zoomIn').on('click', () => svg.zoom(svg.zoom() * 1.2));
            $('#zoomOut').on('click', () => svg.zoom(svg.zoom() / 1.2));
            $('#reset').on('click', () => {
                svg.zoom(1);
                svg.viewbox(0, 0, 800, 800);
            });
        }

        // Floor Selector
        function initializeFloorSelector() {
            $('.floor-btn-panel').on('click', function() {
                const floorId = $(this).data('floor-id');
                $('.floor-btn-panel').removeClass('active');
                $(this).addClass('active');
                config.currentFloorId = floorId;
                loadLocations(floorId);
            });
        }

        // Toolbar
        function initializeToolbar() {
            $('#toggleEditMode').on('click', function() {
                config.isEditMode = !config.isEditMode;
                $(this).toggleClass('active');
                $('#editModeToolbar').toggle(config.isEditMode);

                if (!config.isEditMode) {
                    disableAllEditModes();
                }
            });

            $('#modalEditBtn').on('click', function() {
                setEditModeType('modal');
            });

            $('#moveEditBtn').on('click', function() {
                setEditModeType('move');
            });

            $('#resizeEditBtn').on('click', function() {
                setEditModeType('resize');
            });

            $('#createLocationBtn').on('click', function() {
                $('#create_floor_id').val(config.currentFloorId);
                new bootstrap.Modal($('#createLocationModal')).show();
            });

            $('#autoSaveToggle').on('click', function() {
                config.autoSave = !config.autoSave;
                $(this).toggleClass('active');
                $('#autoSaveLabel').text('Auto-guardar: ' + (config.autoSave ? 'ON' : 'OFF'));
                $('#saveAllChanges').toggle(!config.autoSave && config.unsavedChanges.size > 0);
            });

            $('#saveAllChanges').on('click', function() {
                saveAllChanges();
            });
        }

        function setEditModeType(type) {
            config.editModeType = type;
            $('.toolbar-mode-btn').removeClass('active');
            $(`#${type}EditBtn`).addClass('active');

            disableAllEditModes();

            if (type === 'move') {
                enableMoveMode();
            } else if (type === 'resize') {
                enableResizeMode();
            }
        }

        function disableAllEditModes() {
            $('.location-group').off('mousedown');
            $('.location-group').removeClass('handle');
            $('.resize-handle').remove();
        }

        function enableMoveMode() {
            $('.location-group').addClass('handle').each(function() {
                const locationId = $(this).data('location-id');
                makeDraggable(this, locationId);
            });
        }

        function enableResizeMode() {
            $('.location-group').each(function() {
                const locationId = $(this).data('location-id');
                addResizeHandles(this, locationId);
            });
        }

        // Search
        function initializeSearch() {
            $('#shelfSearch').on('input', function() {
                const query = $(this).val().toLowerCase();

                $('.location-group').each(function() {
                    const text = $(this).find('.location-text').text().toLowerCase();
                    const match = text.includes(query);

                    $(this).toggleClass('highlighted', match && query.length > 0);
                    $(this).toggle(match || query.length === 0);
                });
            });
        }

        // Load Locations
        function loadLocations(floorId) {
            $('.loading').show();
            world.find('.location-group').forEach(el => el.remove());
            config.locations.clear();

            $.ajax({
                url: config.routes.locations,
                method: 'GET',
                data: { floor_id: floorId },
                success: function(response) {
                    $('.loading').hide();

                    if (response.locations && response.locations.length > 0) {
                        response.locations.forEach(location => {
                            config.locations.set(location.id, location);
                            renderLocation(location);
                        });
                    }
                },
                error: function() {
                    $('.loading').hide();
                    alert('Error al cargar las locations');
                }
            });
        }

        // Render Location
        function renderLocation(location) {
            const group = world.group().addClass('location-group').attr('data-location-id', location.id);

            const rect = group.rect(location.width, location.height)
                .move(location.x, location.y)
                .addClass('location-rect')
                .addClass(`status-${location.status || 'empty'}`);

            const text = group.text(location.name)
                .addClass('location-text')
                .move(
                    location.x + location.width / 2,
                    location.y + location.height / 2
                )
                .attr('text-anchor', 'middle')
                .attr('dominant-baseline', 'middle');

            group.on('click', function(e) {
                if (config.isEditMode && config.editModeType === 'modal') {
                    openEditModal(location);
                } else if (!config.isEditMode) {
                    showLocationInfo(location);
                }
            });
        }

        // Show Location Info
        function showLocationInfo(location) {
            const info = `
                <p><strong>Nombre:</strong> <span>${location.name}</span></p>
                <p><strong>Código:</strong> <span>${location.code || 'N/A'}</span></p>
                <p><strong>Capacidad:</strong> <span>${location.capacity || 'N/A'}</span></p>
                <p><strong>Estado:</strong> <span>${location.status || 'empty'}</span></p>
                <button class="btn-edit-location" data-id="${location.id}">Editar</button>
                <button class="btn-sections" data-id="${location.id}">Ver Secciones</button>
            `;

            $('#shelfInfo').html(info);
            $('#shelfInfoContainer').show();

            $('.btn-edit-location').on('click', function() {
                const id = $(this).data('id');
                const loc = config.locations.get(id);
                if (loc) openEditModal(loc);
            });

            $('.btn-sections').on('click', function() {
                const id = $(this).data('id');
                const url = config.routes.sections.replace(':id', id);
                window.location.href = url;
            });
        }

        // Modals
        function initializeModals() {
            $('#saveCreateLocation').on('click', function() {
                const formData = new FormData($('#createLocationForm')[0]);

                $.ajax({
                    url: config.routes.create,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            bootstrap.Modal.getInstance($('#createLocationModal')).hide();
                            loadLocations(config.currentFloorId);
                            $('#createLocationForm')[0].reset();
                        }
                    },
                    error: function() {
                        alert('Error al crear location');
                    }
                });
            });

            $('#saveEditLocation').on('click', function() {
                const formData = new FormData($('#editLocationForm')[0]);
                const locationId = $('#edit_location_id').val();

                $.ajax({
                    url: config.routes.update,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            bootstrap.Modal.getInstance($('#editLocationModal')).hide();
                            loadLocations(config.currentFloorId);
                            config.unsavedChanges.delete(parseInt(locationId));
                            updateUnsavedCount();
                        }
                    },
                    error: function() {
                        alert('Error al actualizar location');
                    }
                });
            });

            $('#deleteLocation').on('click', function() {
                if (!confirm('¿Estás seguro de eliminar esta location?')) return;

                const locationId = $('#edit_location_id').val();
                const url = config.routes.delete.replace(':id', locationId);

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.success) {
                            bootstrap.Modal.getInstance($('#editLocationModal')).hide();
                            loadLocations(config.currentFloorId);
                        }
                    },
                    error: function() {
                        alert('Error al eliminar location');
                    }
                });
            });
        }

        function openEditModal(location) {
            $('#edit_location_id').val(location.id);
            $('#edit_floor_id').val(location.floor_id);
            $('#edit_name').val(location.name);
            $('#edit_code').val(location.code);
            $('#edit_capacity').val(location.capacity);
            $('#edit_x').val(location.x);
            $('#edit_y').val(location.y);
            $('#edit_width').val(location.width);
            $('#edit_height').val(location.height);
            $('#edit_description').val(location.description);

            new bootstrap.Modal($('#editLocationModal')).show();
        }

        // Draggable
        function makeDraggable(element, locationId) {
            let startX, startY, isDragging = false;

            $(element).on('mousedown', function(e) {
                isDragging = true;
                const bbox = element.getBBox();
                startX = e.clientX - bbox.x;
                startY = e.clientY - bbox.y;

                $(document).on('mousemove.drag', function(e) {
                    if (!isDragging) return;

                    const newX = e.clientX - startX;
                    const newY = e.clientY - startY;

                    $(element).find('.location-rect').attr('x', newX).attr('y', newY);
                    $(element).find('.location-text').attr('x', newX + location.width / 2).attr('y', newY + location.height / 2);
                });

                $(document).on('mouseup.drag', function() {
                    isDragging = false;
                    $(document).off('mousemove.drag mouseup.drag');

                    const bbox = element.getBBox();
                    const location = config.locations.get(locationId);
                    location.x = bbox.x;
                    location.y = bbox.y;

                    if (config.autoSave) {
                        saveLocation(location);
                    } else {
                        config.unsavedChanges.set(locationId, location);
                        updateUnsavedCount();
                    }
                });
            });
        }

        // Resize Handles
        function addResizeHandles(element, locationId) {
            const bbox = element.getBBox();
            const handle = svg.circle(10)
                .addClass('resize-handle')
                .move(bbox.x2 - 5, bbox.y2 - 5);

            let isDragging = false;

            handle.on('mousedown', function(e) {
                e.stopPropagation();
                isDragging = true;

                $(document).on('mousemove.resize', function(e) {
                    if (!isDragging) return;

                    const location = config.locations.get(locationId);
                    const newWidth = Math.max(40, e.clientX - location.x);
                    const newHeight = Math.max(20, e.clientY - location.y);

                    $(element).find('.location-rect').size(newWidth, newHeight);
                    $(element).find('.location-text')
                        .move(location.x + newWidth / 2, location.y + newHeight / 2);

                    handle.move(location.x + newWidth - 5, location.y + newHeight - 5);
                });

                $(document).on('mouseup.resize', function() {
                    isDragging = false;
                    $(document).off('mousemove.resize mouseup.resize');

                    const rect = $(element).find('.location-rect')[0];
                    const location = config.locations.get(locationId);
                    location.width = parseFloat(rect.getAttribute('width'));
                    location.height = parseFloat(rect.getAttribute('height'));

                    if (config.autoSave) {
                        saveLocation(location);
                    } else {
                        config.unsavedChanges.set(locationId, location);
                        updateUnsavedCount();
                    }
                });
            });
        }

        // Save Functions
        function saveLocation(location) {
            $.ajax({
                url: config.routes.update,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {
                    id: location.id,
                    x: location.x,
                    y: location.y,
                    width: location.width,
                    height: location.height
                },
                success: function(response) {
                    if (response.success) {
                        config.unsavedChanges.delete(location.id);
                        updateUnsavedCount();
                    }
                }
            });
        }

        function saveAllChanges() {
            const promises = [];

            config.unsavedChanges.forEach((location, id) => {
                promises.push(saveLocation(location));
            });

            Promise.all(promises).then(() => {
                config.unsavedChanges.clear();
                updateUnsavedCount();
            });
        }

        function updateUnsavedCount() {
            const count = config.unsavedChanges.size;
            $('#unsavedCount').text(`Guardar (${count})`);
            $('#saveAllChanges').toggle(!config.autoSave && count > 0);
        }
    </script>
</body>
</html>
@endsection
