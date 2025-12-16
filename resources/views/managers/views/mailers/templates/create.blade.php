@extends('managers.includes.layout')

@section('page_title', 'Crear Plantilla de Email')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb Card --}}
    @include('managers.includes.card', [
        'title' => 'Crear Nueva Plantilla de Email',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/home')],
            ['label' => 'Configuración', 'url' => route('manager.settings')],
            ['label' => 'Plantillas', 'url' => route('manager.settings.mailers.templates.index')],
            ['label' => 'Crear', 'active' => true]
        ]
    ])

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-circle fs-4 me-2 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-2">Errores de Validación</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Form --}}
    <form method="POST" action="{{ route('manager.settings.mailers.templates.store') }}" id="formCreate">
        @csrf

        <div class="row g-3">
            {{-- Left Column: Editor --}}
            <div class="col-12 col-lg-8">
                <div class="card">
                    {{-- Header --}}
                    <div class="card-header border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold">Editor de código</h5>
                                    <small class="text-muted">Crea el contenido de la plantilla</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge text-info">
                                    <i class="fas fa-keyboard me-1"></i>Ctrl+S para guardar
                                </span>
                                <span class="badge bg-black text-white" id="editorStatus">
                                    Listo
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Toolbar --}}
                    <div class="card-body border-bottom p-3">
                        <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
                            <!-- Action Buttons Group -->
                            <div class="btn-group mb-2" role="group" aria-label="Editor actions">
                                <button type="button" class="btn btn-secondary" id="btnFormatCode"
                                        data-bs-toggle="tooltip" title="Formatear código HTML">
                                    <i class="fas fa-wand-magic-sparkles"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnRefreshPreview"
                                        data-bs-toggle="tooltip" title="Actualizar vista previa">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <!-- Variable Selector -->
                            <div class="flex-grow-1 mb-2" style="max-width: 400px;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-code me-1"></i>Variable
                                    </span>
                                    <select class="form-select form-select-sm" id="variableSelector">
                                        <option value="">-- Selecciona una variable --</option>
                                    </select>
                                    <button class="btn btn-primary" type="button" id="btnInsertVariable"
                                            data-bs-toggle="tooltip" title="Insertar variable en el cursor">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <small class="text-muted d-none d-md-inline">
                                    <i class="fas fa-lightbulb me-1"></i>Usa Emmet para escribir más rápido
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Template Info --}}
                    <div class="card-body border-bottom">
                        <div class="row g-3">
                            <div class="col-12 col-md-12">
                                <label for="key" class="form-label fw-semibold">
                                    Clave (Key) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('key') is-invalid @enderror"
                                       id="key" name="key" value="{{ old('key') }}"
                                       placeholder="order_confirmation" required>
                                <small class="text-muted">Identificador único para usar en código</small>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="name" class="form-label fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Confirmación de Pedido" required>
                                <small class="text-muted">Nombre descriptivo de la plantilla</small>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="subject" class="form-label fw-semibold">
                                    Asunto del email <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                       id="subject" name="subject" value="{{ old('subject') }}"
                                       placeholder="Confirmación de pedido #{ORDER_NUMBER}" required>
                                <small class="text-muted">Puedes usar variables: {CUSTOMER_NAME}, {ORDER_NUMBER}</small>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="preheader" class="form-label fw-semibold">
                                    Preheader <span class="text-muted small">(Opcional)</span>
                                </label>
                                <input type="text" class="form-control @error('preheader') is-invalid @enderror"
                                       id="preheader" name="preheader" value="{{ old('preheader') }}"
                                       placeholder="Texto de vista previa en bandeja de entrada"
                                       maxlength="255">
                                <small class="text-muted">Aparece junto al asunto en Gmail, Outlook, etc.</small>
                                @error('preheader')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="module" class="form-label fw-semibold">
                                    Módulo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('module') is-invalid @enderror"
                                        id="module" name="module" required>
                                    <option value="">-- Selecciona --</option>
                                    <option value="core" @if(old('module', $module ?? '') == 'core') selected @endif>Core (Sistema)</option>
                                    <option value="documents" @if(old('module', $module ?? '') == 'documents') selected @endif>Documentos</option>
                                    <option value="orders" @if(old('module', $module ?? '') == 'orders') selected @endif>Órdenes</option>
                                    <option value="notifications" @if(old('module', $module ?? '') == 'notifications') selected @endif>Notificaciones</option>
                                </select>
                                <small class="text-muted">Determina las variables disponibles</small>
                                @error('module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="layout_id" class="form-label fw-semibold">
                                    Layout base <span class="text-muted small">(Opcional)</span>
                                </label>
                                <select class="form-select select2 @error('layout_id') is-invalid @enderror" id="layout_id" name="layout_id">
                                    <option value="">Sin layout (solo contenido)</option>
                                    @if(isset($layouts))
                                        @foreach($layouts as $layout)
                                            <option value="{{ $layout->id }}"
                                                @if(old('layout_id') == $layout->id) selected @endif>
                                                {{ $layout->alias }} - {{ $layout->translate($currentLangId)?->subject ?? 'Sin nombre' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Layout personalizado para esta plantilla</small>
                                @error('layout_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="lang_id" class="form-label fw-semibold">
                                    Idioma base <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('lang_id') is-invalid @enderror" id="lang_id" name="lang_id" required>
                                    <option value="">Selecciona un idioma</option>
                                    @if(isset($langs))
                                        @foreach($langs as $language)
                                            <option value="{{ $language->id }}" @if(old('lang_id', $langId ?? '') == $language->id) selected @endif>
                                                {{ $language->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Se crearán versiones para todos los idiomas</small>
                                @error('lang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold">
                                    Descripción <span class="text-muted small">(Opcional)</span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2"
                                          placeholder="Descripción breve de para qué se usa esta plantilla">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_enabled" name="is_enabled" value="1" checked>
                                    <label class="form-check-label" for="is_enabled">
                                        <strong>Plantilla habilitada</strong>
                                        <small class="d-block text-muted">Si desactivas esta opción, la plantilla no se podrá usar en el sistema</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_protected" name="is_protected" value="1">
                                    <label class="form-check-label" for="is_protected">
                                        <strong>Plantilla protegida</strong>
                                        <small class="d-block text-muted">Si activas esta opción, la plantilla no podrá ser eliminada sin desactivar primero la protección</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Multi-language Alert --}}
                        <div class="alert alert-info border-0 mb-0 mt-3 d-flex align-items-start">
                            <i class="fas fa-info-circle fs-5 me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>Auto-generación de traducciones</strong>
                                <p class="mb-0 mt-1 small">
                                    Al crear esta plantilla, se generarán automáticamente versiones para todos los idiomas disponibles en el sistema.
                                    El contenido inicial será el mismo para todos los idiomas, y podrás editarlos individualmente después.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tabs: Code Editor & Preview --}}
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-bottom" id="editorTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="code-tab" data-bs-toggle="tab" data-bs-target="#code-panel" type="button" role="tab" aria-controls="code-panel" aria-selected="true">
                                    <i class="fas fa-code me-2"></i>Editor de Código
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-panel" type="button" role="tab" aria-controls="preview-panel" aria-selected="false">
                                    <i class="fas fa-eye me-2"></i>Vista Previa
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="editorTabsContent">
                            <div class="tab-pane fade show active" id="code-panel" role="tabpanel" aria-labelledby="code-tab">
                                <textarea class="form-control" id="content" name="content" style="display: none;">{{ old('content', $baseContent ?? '') }}</textarea>
                            </div>
                            <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel" aria-labelledby="preview-tab">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                                    <div>
                                        <h6 class="mb-1 fw-semibold text-dark">
                                            <i class="fas fa-eye me-2 text-primary"></i>Vista previa del email
                                        </h6>
                                        <small class="text-muted d-block">
                                            Cambia entre vistas de escritorio y móvil para ver cómo se verá tu email
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Device preview">
                                            <button type="button" class="btn btn-outline-primary active" id="btnDesktopViewCreate" data-width="100%"
                                                    data-bs-toggle="tooltip" title="Vista Desktop (100%)">
                                                <i class="fas fa-desktop me-1"></i><span class="d-none d-sm-inline">Desktop</span>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="btnMobileViewCreate" data-width="375px"
                                                    data-bs-toggle="tooltip" title="Vista Mobile (375px)">
                                                <i class="fas fa-mobile-screen me-1"></i><span class="d-none d-sm-inline">Mobile</span>
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success" id="btnRefreshPreviewCreate"
                                                data-bs-toggle="tooltip" title="Actualizar vista previa">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="previewContainerTab" style="min-height: 550px; overflow-y: auto; background: #f8f9fa; display: flex; justify-content: center; padding: 20px;">
                                    <div class="text-center py-5">
                                        <i class="fas fa-code fs-1 text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Vista previa en vivo</p>
                                        <small class="text-muted">Cambia a la pestaña "Vista Previa" para ver el resultado</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="card-footer bg-white border-top">
                        <button type="submit" class="btn btn-primary w-100  mb-1">
                            Crear
                        </button>
                        <a href="{{ route('manager.settings.mailers.templates.index') }}" class="btn btn-secondary w-100">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Variables --}}
            <div class="col-12 col-lg-4">
                {{-- Variables Panel --}}
                <div class="card mb-3" id="variablesCard">
                    <div class="card-header border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-code me-1"></i>Variables disponibles
                            </h6>
                            <button type="button" class="btn btn-sm btn-black" id="btnLoadVariables"
                                    data-bs-toggle="tooltip" title="Recargar variables">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="variablesPanel" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fs-3 mb-2 d-block"></i>
                            <p class="mb-0 small">Selecciona un módulo para ver las variables disponibles</p>
                        </div>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="card">
                    <div class="card-header bg-info-subtle border-bottom p-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-lightbulb me-2"></i>Atajos de teclado
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <kbd>Ctrl</kbd> + <kbd>S</kbd> - Guardar
                            </li>
                            <li class="mb-2">
                                <kbd>Ctrl</kbd> + <kbd>Space</kbd> - Autocompletar
                            </li>
                            <li class="mb-2">
                                <kbd>Ctrl</kbd> + <kbd>/</kbd> - Comentar línea
                            </li>
                            <li class="mb-2">
                                <kbd>Tab</kbd> - Expandir Emmet
                            </li>
                            <li>
                                <strong>Ejemplo Emmet:</strong><br>
                                <code>div.container>div.row</code>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/monokai.min.css">
<style>
.CodeMirror {
    font-size: 12px;
    line-height: 1.6;
    height: 550px !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 4px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    background-color: #ffffff !important;
    color: #000000 !important;
}

.CodeMirror-scroll {
    background-color: #ffffff !important;
}

.CodeMirror-sizer {
    background-color: #ffffff !important;
}

.CodeMirror-lines {
    background-color: #ffffff !important;
}

.CodeMirror-gutters {
    background-color: #f5f6f8 !important;
    border-right: 1px solid #ddd !important;
}

.CodeMirror-linenumber {
    color: #666 !important;
    font-weight: 500;
}

/* Cursor with blinking animation */
@keyframes cursorBlink {
    0%, 49% {
        border-left-color: #90bb13 !important;
        box-shadow: 0 0 8px rgba(144, 187, 19, 0.6) !important;
    }
    50%, 100% {
        border-left-color: transparent !important;
        box-shadow: none !important;
    }
}

.CodeMirror-cursor {
    border-left: 3px solid #90bb13 !important;
    height: 100% !important;
    animation: cursorBlink 1s infinite !important;
}

.CodeMirror.CodeMirror-focused .CodeMirror-cursor {
    border-left-color: #90bb13 !important;
    animation: cursorBlink 1s infinite !important;
}

/* Syntax highlighting - Visible on white background */
.cm-tag {
    color: #0066cc !important;
    font-weight: 500 !important;
    background-color: transparent !important;
}

.cm-attribute {
    color: #ff6600 !important;
    font-weight: 500 !important;
    background-color: transparent !important;
}

.cm-string {
    color: #00aa00 !important;
    background-color: transparent !important;
}

.cm-string-2 {
    color: #00aa00 !important;
    background-color: transparent !important;
}

.cm-number {
    color: #dd1111 !important;
    background-color: transparent !important;
}

.cm-atom {
    color: #0066cc !important;
    background-color: transparent !important;
}

.cm-keyword {
    color: #0066cc !important;
    font-weight: 500 !important;
    background-color: transparent !important;
}

.cm-variable {
    color: #333333 !important;
    background-color: transparent !important;
}

.cm-variable-2 {
    color: #333333 !important;
    background-color: transparent !important;
}

.cm-variable-3 {
    color: #333333 !important;
    background-color: transparent !important;
}

.cm-property {
    color: #333333 !important;
    background-color: transparent !important;
}

.cm-comment {
    color: #999999 !important;
    font-style: italic !important;
    background-color: transparent !important;
}

.cm-meta {
    color: #999999 !important;
    background-color: transparent !important;
}

.cm-qualifier {
    color: #0066cc !important;
    background-color: transparent !important;
}

.cm-builtin {
    color: #0066cc !important;
    background-color: transparent !important;
}

.cm-bracket {
    color: #666666 !important;
    background-color: transparent !important;
}

.cm-tag-name {
    color: #0066cc !important;
    background-color: transparent !important;
    font-weight: 500 !important;
}

.cm-attribute-value {
    color: #00aa00 !important;
    background-color: transparent !important;
}

.cm-html-tag {
    color: #0066cc !important;
    background-color: transparent !important;
}

.cm-operator {
    color: #666666 !important;
    background-color: transparent !important;
}

.cm-error {
    color: #dd1111 !important;
    background-color: transparent !important;
}

.CodeMirror .cm-s-monokai.cm-s-monokai * {
    background-color: transparent !important;
}

.CodeMirror-focused {
    border-color: #90bb13;
    box-shadow: 0 0 0 3px rgba(144, 187, 19, 0.1);
}

.hover-shadow-sm:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transform: translateX(2px);
    transition: all 0.2s ease;
}

.variable-insert {
    transition: all 0.2s ease;
    cursor: pointer;
    padding: 8px;
    border-left: 3px solid transparent;
}

.variable-insert:hover {
    background: #f6f7f9 !important;
    padding: 8px;
}

.variable-insert code {
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 13px;
}

#preview-panel {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

#previewContainer {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    justify-content: center;
    padding: 20px;
}

#previewContainerTab {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Device preview buttons styling */
#preview-panel .btn-group .btn {
    border-color: #d1d5db;
    transition: all 0.2s ease;
}

#preview-panel .btn-group .btn:hover {
    border-color: #90bb13;
    background-color: rgba(144, 187, 19, 0.05);
}

#preview-panel .btn-group .btn.active {
    background-color: #90bb13;
    border-color: #90bb13;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(144, 187, 19, 0.3);
}

#preview-panel .btn-group .btn.active:hover {
    background-color: #7a9a0f;
    border-color: #7a9a0f;
}

/* Refresh button styling */
#btnRefreshPreviewCreate {
    transition: all 0.2s ease;
}

#btnRefreshPreviewCreate:hover:not(:disabled) {
    box-shadow: 0 2px 8px rgba(19, 198, 114, 0.3);
    transform: translateY(-1px);
}

#btnRefreshPreviewCreate:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#previewContainer iframe {
    transition: all 0.3s ease;
}

#previewContainerTab {
    transition: max-width 0.3s ease;
}

.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
}

/* Tooltip Styling - Green Theme */
.tooltip-inner {
    background-color: #90bb13 !important;
    color: #fff !important;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(144, 187, 19, 0.3);
}

.bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before,
.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #90bb13 !important;
}

.bs-tooltip-auto[data-popper-placement^="right"] .tooltip-arrow::before,
.bs-tooltip-end .tooltip-arrow::before {
    border-right-color: #90bb13 !important;
}

.bs-tooltip-auto[data-popper-placement^="bottom"] .tooltip-arrow::before,
.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #90bb13 !important;
}

.bs-tooltip-auto[data-popper-placement^="left"] .tooltip-arrow::before,
.bs-tooltip-start .tooltip-arrow::before {
    border-left-color: #90bb13 !important;
}

/* Selection Color - Green (#90bb13) with white text */
.CodeMirror-selected {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line::selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line > span::selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line > span > span::selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line::-moz-selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line > span::-moz-selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-line > span > span::-moz-selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror ::selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror ::-moz-selection {
    background-color: #90bb13 !important;
    color: #fff !important;
}

/* Active Line */
.CodeMirror-activeline {
    background-color: #ffffff !important;
}

.CodeMirror-activeline .CodeMirror-linenumber {
    background-color: #f0f0f0 !important;
    color: #333 !important;
}

.CodeMirror-activeline .cm-tag {
    color: #0066cc !important;
}

.CodeMirror-activeline .cm-attribute {
    color: #ff6600 !important;
}

.CodeMirror-activeline .cm-string {
    color: #00aa00 !important;
}

.CodeMirror-activeline .cm-variable,
.CodeMirror-activeline .cm-property {
    color: #333333 !important;
}

.CodeMirror-linenumber {
    background-color: #f5f6f8 !important;
}

.CodeMirror-line {
    background-color: #ffffff !important;
    color: #000000 !important;
}

.CodeMirror {
    caret-color: #90bb13 !important;
}

.CodeMirror span {
    background-color: transparent !important;
    color: #333333 !important;
}

.CodeMirror pre {
    background-color: transparent !important;
    color: #333333 !important;
}

.cm-tab {
    color: #999 !important;
}

/* Matching Brackets */
.CodeMirror-matchingbracket {
    background-color: rgba(144, 187, 19, 0.15) !important;
    color: #0066cc !important;
    font-weight: bold !important;
    border-bottom: 2px solid #90bb13 !important;
    text-decoration: underline !important;
    text-decoration-color: #90bb13 !important;
    text-decoration-thickness: 2px !important;
}

.CodeMirror-nonmatchingbracket {
    background-color: rgba(250, 137, 107, 0.15) !important;
    color: #0066cc !important;
    font-weight: bold !important;
    border-bottom: 2px solid #FA896B !important;
    text-decoration: underline !important;
    text-decoration-color: #FA896B !important;
    text-decoration-thickness: 2px !important;
}

/* Autocomplete Dropdown */
.CodeMirror-hints {
    background-color: #1e1e1e !important;
    border: 2px solid #90bb13 !important;
    border-radius: 6px !important;
    box-shadow: 0 4px 16px rgba(144, 187, 19, 0.25) !important;
    max-height: 300px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12px !important;
    z-index: 9999 !important;
}

.CodeMirror-hint {
    color: #e0e0e0 !important;
    padding: 8px 12px !important;
    border-bottom: 1px solid #333 !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
}

.CodeMirror-hint:last-child {
    border-bottom: none !important;
}

.CodeMirror-hint-active,
.CodeMirror-hint:hover {
    background-color: #90bb13 !important;
    color: #fff !important;
    font-weight: 600 !important;
    padding-left: 16px !important;
    box-shadow: inset 0 0 0 2px rgba(144, 187, 19, 0.3) !important;
}

.CodeMirror-hint-active {
    background-color: #90bb13 !important;
    color: #fff !important;
}

.CodeMirror-Emmet-abbreviation {
    color: #90bb13 !important;
}

@media (max-width: 991px) {
    .CodeMirror {
        height: 400px !important;
    }
}

/* Variable Cards - Grid Layout */
.variable-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    user-select: none;
}

.variable-card:hover {
    background-color: #f0f4f8;
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
}

.variable-card:active {
    transform: translateY(0);
}

.variable-code {
    font-size: 0.85rem;
    font-weight: 600;
    color: #0d6efd;
    background: none;
    padding: 0;
    word-break: break-word;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closetag.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closebrackets.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/html-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/css-hint.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.css">
<script src="https://cdn.jsdelivr.net/npm/emmet-codemirror@1.1.106/emmet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify-html.js"></script>

<script>
console.log('🎨 CodeMirror Email Template Creator Loading...');

$(document).ready(function() {
    console.log('✅ DOM Ready - Initializing CodeMirror');

    // Initialize Bootstrap Tooltips
    $('[data-bs-toggle="tooltip"]').each(function() {
        new bootstrap.Tooltip(this);
    });

    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            allowClear: false,
            width: '100%'
        });
    }

    const $contentTextarea = $('#content');
    if ($contentTextarea.length === 0) {
        console.error('❌ Textarea #content not found!');
        return;
    }

    console.log('📝 Textarea found, initializing CodeMirror...');

    // Initialize CodeMirror
    const editor = CodeMirror.fromTextArea($contentTextarea[0], {
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        lineWrapping: true,
        indentUnit: 4,
        tabSize: 4,
        indentWithTabs: false,
        autoCloseTags: true,
        autoCloseBrackets: true,
        styleActiveLine: true,
        matchBrackets: true,
        highlightSelectionMatches: {showToken: /\w/, annotateScrollbar: true},
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'Ctrl-/': 'toggleComment',
            'Tab': 'emmetExpandAbbreviation',
            'Ctrl-Alt-Enter': 'emmetWrapWithAbbreviation'
        }
    });

    console.log('✨ CodeMirror initialized successfully!', editor);

    // Initialize Emmet
    try {
        if (typeof emmetCodeMirror !== 'undefined') {
            emmetCodeMirror(editor);
            console.log('✅ Emmet initialized successfully!');
        }
    } catch (e) {
        console.warn('⚠️ Emmet not available:', e);
    }

    // Smart Autocomplete - For email templates
    CodeMirror.registerHelper('hint', 'smartHints', function(editor) {
        const cur = editor.getCursor();
        const token = editor.getTokenAt(cur);
        const line = editor.getLine(cur.line);
        const start = token.start;
        const end = cur.ch;
        const word = token.string.substring(0, end - start).toLowerCase();

        const beforeCursor = line.substring(0, cur.ch);
        const insideStyleAttr = beforeCursor.match(/style\s*=\s*["'][^"']*$/);
        const insideTag = beforeCursor.match(/<[^>]*$/);
        const afterTagName = beforeCursor.match(/<\w+\s+[\w\s-]*$/);
        const insideHref = beforeCursor.match(/href\s*=\s*["'][^"']*$/);
        const insideSrc = beforeCursor.match(/src\s*=\s*["'][^"']*$/);
        const insideAlt = beforeCursor.match(/alt\s*=\s*["'][^"']*$/);

        let completions = [];

        if (insideStyleAttr) {
            const cssProperties = [
                'color', 'background-color', 'background', 'opacity',
                'width', 'height', 'max-width', 'min-width', 'min-height', 'max-height',
                'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
                'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
                'border', 'border-color', 'border-radius', 'border-width', 'border-style',
                'border-top', 'border-bottom', 'border-left', 'border-right',
                'font-size', 'font-weight', 'font-family', 'font-style', 'line-height',
                'text-align', 'text-decoration', 'text-transform', 'letter-spacing',
                'text-indent', 'white-space', 'word-break', 'word-wrap',
                'display', 'position', 'overflow', 'overflow-x', 'overflow-y',
                'visibility', 'z-index', 'float', 'clear',
                'flex', 'flex-direction', 'flex-wrap', 'flex-grow', 'flex-shrink',
                'justify-content', 'align-items', 'align-content', 'gap',
                'box-shadow', 'text-shadow', 'transform', 'transition', 'animation'
            ];

            completions = cssProperties
                .filter(prop => prop.toLowerCase().startsWith(word))
                .map(prop => ({text: prop + ': ', displayText: `🎨 ${prop}`}));
        }
        else if (insideHref) {
            const urls = [
                '{SITE_URL}', '{RESET_LINK}', 'https://', 'http://', 'mailto:', 'tel:',
                '#', 'javascript:void(0)'
            ];

            completions = urls
                .filter(url => url.toLowerCase().startsWith(word))
                .map(url => ({text: url, displayText: `🔗 ${url}`}));
        }
        else if (insideSrc) {
            const images = [
                '{LOGO_URL}', 'https://', 'http://', 'data:image/png;base64,'
            ];

            completions = images
                .filter(img => img.toLowerCase().startsWith(word))
                .map(img => ({text: img, displayText: `🖼️ ${img}`}));
        }
        else if (insideAlt) {
            const alts = [
                'Logo', 'Banner', 'Product Image', 'Company Logo', 'Hero Image', 'Icon',
                'Button Image', 'Social Icon', 'Header Image', 'Footer Image'
            ];

            completions = alts
                .filter(alt => alt.toLowerCase().startsWith(word))
                .map(alt => ({text: alt, displayText: `📝 ${alt}`}));
        }
        else if (afterTagName) {
            const htmlAttributes = [
                'id', 'class', 'style', 'dir',
                'href', 'src', 'alt', 'title', 'target',
                'name', 'value', 'type', 'placeholder', 'required', 'disabled', 'readonly',
                'cellpadding', 'cellspacing', 'border', 'bordercolor', 'align', 'valign',
                'colspan', 'rowspan', 'bgcolor', 'width', 'height',
                'role', 'aria-label', 'aria-describedby',
                'onclick', 'onload', 'onmouseover', 'onmouseout',
                'action', 'method', 'enctype', 'tabindex', 'lang'
            ];

            completions = htmlAttributes
                .filter(attr => attr.toLowerCase().startsWith(word))
                .map(attr => ({text: attr + '="', displayText: `🏷️ ${attr}`}));
        }
        else if (insideTag) {
            const classes = [
                'email-wrapper', 'email-container', 'email-body', 'email-footer',
                'email-header', 'email-content', 'email-section',
                'container', 'container-fluid', 'row', 'col', 'col-12', 'col-6', 'col-4', 'col-3',
                'col-md-6', 'col-lg-6', 'col-xl-6',
                'mt-1', 'mt-2', 'mt-3', 'mt-4', 'mt-5', 'mb-1', 'mb-2', 'mb-3', 'mb-4', 'mb-5',
                'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'px-2', 'py-2', 'pt-2', 'pb-2',
                'ms-1', 'me-1', 'ms-auto',
                'd-flex', 'd-block', 'd-none', 'd-inline', 'd-grid',
                'flex-column', 'flex-row', 'justify-content-center', 'justify-content-between',
                'justify-content-end', 'align-items-center', 'align-items-start', 'gap-2', 'gap-3',
                'text-primary', 'text-success', 'text-danger', 'text-warning', 'text-muted',
                'bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-light',
                'text-center', 'text-end', 'text-start', 'fw-bold', 'fw-normal', 'small', 'lead',
                'btn', 'btn-primary', 'btn-secondary', 'btn-success', 'btn-danger',
                'btn-outline-primary', 'btn-lg', 'btn-sm',
                'card', 'card-body', 'card-header', 'card-footer', 'card-title', 'section-block',
                'table', 'table-striped', 'table-hover', 'table-bordered', 'table-responsive',
                'form-control', 'form-group', 'form-label', 'input-group',
                'alert', 'alert-primary', 'alert-success', 'alert-danger', 'alert-warning',
                'rounded', 'shadow', 'border', 'h-100', 'w-100', 'overflow-hidden',
                'text-truncate', 'text-uppercase', 'text-lowercase'
            ];

            completions = classes
                .filter(cls => cls.toLowerCase().startsWith(word))
                .map(cls => ({text: cls, displayText: `📦 ${cls}`}));
        }
        else {
            const htmlTags = [
                'html', 'head', 'body', 'meta', 'title',
                'div', 'section', 'header', 'footer', 'main', 'article',
                'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
                'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'span', 'strong', 'em', 'u', 'code', 'pre', 'blockquote',
                'ul', 'ol', 'li', 'dl', 'dt', 'dd',
                'img', 'picture', 'figure', 'figcaption',
                'a', 'nav', 'menu',
                'form', 'input', 'button', 'label', 'select', 'textarea', 'fieldset',
                'br', 'hr', 'small', 'mark', 'del', 'ins', 'sub', 'sup',
                'aside', 'address', 'time'
            ];

            completions = htmlTags
                .filter(tag => tag.toLowerCase().startsWith(word))
                .map(tag => ({text: tag, displayText: `🏷️ ${tag}`}));
        }

        return {
            from: CodeMirror.Pos(cur.line, start),
            to: CodeMirror.Pos(cur.line, end),
            list: completions
        };
    });

    // Template Variables Hint
    CodeMirror.registerHelper('hint', 'variables', function(editor) {
        const cur = editor.getCursor();
        const token = editor.getTokenAt(cur);
        const line = editor.getLine(cur.line);
        const start = token.start;
        const end = cur.ch;

        const beforeCursor = line.substring(0, cur.ch);
        if (!beforeCursor.includes('{')) return;

        const word = token.string.substring(0, end - start).toUpperCase();

        const templateVariables = [
            'SITE_NAME', 'SITE_URL', 'SITE_EMAIL', 'LOGO_URL',
            'COMPANY_NAME', 'COMPANY_ADDRESS', 'COMPANY_CITY', 'COMPANY_STATE', 'COMPANY_ZIP',
            'COMPANY_COUNTRY', 'COMPANY_PHONE', 'COMPANY_EMAIL', 'COMPANY_WEBSITE',
            'CURRENT_YEAR', 'CURRENT_MONTH', 'CURRENT_DAY', 'CURRENT_DATE', 'CURRENT_TIME',
            'RECIPIENT_EMAIL', 'CUSTOMER_NAME', 'CUSTOMER_FIRST_NAME', 'CUSTOMER_LAST_NAME',
            'CUSTOMER_PHONE', 'CUSTOMER_ADDRESS',
            'EMAIL_SUBJECT', 'EMAIL_TITLE', 'CONTENT', 'FOOTER_CONTENT',
            'RESET_LINK', 'CONFIRM_LINK', 'ACTIVATION_LINK', 'UNSUBSCRIBE_LINK',
            'ORDER_ID', 'ORDER_NUMBER', 'ORDER_TOTAL', 'ORDER_STATUS', 'ORDER_DATE',
            'DOCUMENT_TYPE', 'UPLOAD_LINK', 'EXPIRATION_DATE',
            'CURRENT_YEAR_FULL', 'MONTH_NAME', 'DAY_NAME'
        ];

        const completions = templateVariables
            .filter(v => v.startsWith(word))
            .map(v => ({text: v + '}', displayText: `🔤 {${v}}`}));

        return {
            from: CodeMirror.Pos(cur.line, start),
            to: CodeMirror.Pos(cur.line, end),
            list: completions
        };
    });

    // Enable autocomplete on input
    editor.on('inputRead', function(instance, changeObj) {
        if (changeObj.text[0] === ' ' || /\w/.test(changeObj.text[0]) || changeObj.text[0] === '-') {
            CodeMirror.commands.autocomplete(instance, null, {async: true});
        }
    });

    console.log('📊 Editor has', editor.lineCount(), 'lines');

    let previewTimeout;
    let hasChanges = false;

    // Update Editor Status
    function updateEditorStatus(status, icon = 'check-circle', color = 'success') {
        $('#editorStatus')
            .html(`<i class="fas fa-${icon} me-1"></i>${status}`)
            .attr('class', `badge bg-${color}`);
    }

    // Update Preview (local, no backend call for create)
    function updatePreview() {
        const html = editor.getValue();
        const $container = $('#previewContainerTab');
        const $iframe = $('<iframe>')
            .css({
                'width': '100%',
                'min-height': '550px',
                'border': 'none',
                'display': 'block',
                'background': 'white'
            });

        $container.empty().append($iframe);
        $iframe[0].srcdoc = html;
    }

    // Update preview when switching to preview tab
    $('#preview-tab').on('shown.bs.tab', function (e) {
        updatePreview();
    });

    // Load Variables based on module
    function loadVariables() {
        const module = $('#module').val();

        if (!module) {
            $('#variablesPanel').html(
                '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle fs-3 mb-2 d-block"></i><p class="mb-0 small">Selecciona un módulo para ver las variables disponibles</p></div>'
            );
            return;
        }

        // Show loading
        $('#variablesPanel').html(
            '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mb-2" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mb-0 small">Cargando variables...</p></div>'
        );

        // Simulate loading variables (in production, this would be an AJAX call)
        setTimeout(function() {
            const moduleVariables = getVariablesForModule(module);
            renderVariables(moduleVariables);
        }, 500);
    }

    // Get variables for a specific module
    function getVariablesForModule(module) {
        const commonVars = [
            {name: 'SITE_NAME', description: 'Nombre del sitio'},
            {name: 'SITE_URL', description: 'URL del sitio'},
            {name: 'SITE_EMAIL', description: 'Email del sitio'},
            {name: 'LOGO_URL', description: 'URL del logo'},
            {name: 'CUSTOMER_NAME', description: 'Nombre del cliente'},
            {name: 'CUSTOMER_EMAIL', description: 'Email del cliente'},
            {name: 'CURRENT_YEAR', description: 'Año actual'},
        ];

        const moduleSpecificVars = {
            'documents': [
                {name: 'DOCUMENT_TYPE', description: 'Tipo de documento'},
                {name: 'UPLOAD_LINK', description: 'Link para subir documento'},
                {name: 'EXPIRATION_DATE', description: 'Fecha de expiración'}
            ],
            'orders': [
                {name: 'ORDER_ID', description: 'ID del pedido'},
                {name: 'ORDER_NUMBER', description: 'Número de pedido'},
                {name: 'ORDER_TOTAL', description: 'Total del pedido'},
                {name: 'ORDER_STATUS', description: 'Estado del pedido'},
                {name: 'ORDER_DATE', description: 'Fecha del pedido'}
            ],
            'notifications': [
                {name: 'NOTIFICATION_TYPE', description: 'Tipo de notificación'},
                {name: 'NOTIFICATION_DATE', description: 'Fecha de la notificación'}
            ],
            'core': [
                {name: 'RESET_LINK', description: 'Link para resetear contraseña'},
                {name: 'CONFIRM_LINK', description: 'Link de confirmación'},
                {name: 'ACTIVATION_LINK', description: 'Link de activación'}
            ]
        };

        return [...commonVars, ...(moduleSpecificVars[module] || [])];
    }

    // Render Variables
    function renderVariables(variableGroups) {
        // Render in sidebar panel
        let html = '';

        $.each(variableGroups, function(idx, variable) {
            html += `<div class="mb-2 pb-2 border-bottom variable-insert" data-variable-name="${variable.name}">`;
            html += `<a class="text-decoration-none d-block" onclick="return false;">`;
            html += `<code class="d-inline-block bg-light px-2 py-1 text-primary fw-bold">{${variable.name}}</code>`;
            html += `</a>`;
            html += `<small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>${variable.description}</small>`;
            html += `</div>`;
        });

        $('#variablesPanel').html(html);

        // Render in top selector (toolbar)
        let selectorOptions = '<option value="">-- Selecciona una variable --</option>';

        $.each(variableGroups, function(idx, variable) {
            selectorOptions += `<option value="${variable.name}" data-description="${variable.description}">{${variable.name}} - ${variable.description}</option>`;
        });

        $('#variableSelector').html(selectorOptions);

        // Add event listeners for sidebar
        $(document).on('click', '.variable-insert', function(e) {
            e.preventDefault();
            const variableName = $(this).data('variable-name');
            insertVariable(variableName);
        });
    }

    // Insert variable at cursor position
    function insertVariable(variableName) {
        const variable = `{${variableName}}`;
        const cursor = editor.getCursor();
        editor.replaceRange(variable, cursor);
        editor.focus();

        toastr.success(`Variable ${variable} insertada`, 'Éxito', {
            timeOut: 1500,
            progressBar: true
        });
    }

    // Format Code
    function formatCode() {
        updateEditorStatus('Formateando...', 'spinner', 'info');
        const code = editor.getValue();

        try {
            const indentSize = 4;
            const indent = ' ';
            let result = [];
            let indentLevel = 0;

            const selfClosingTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

            let processed = code
                .replace(/>\s*</g, '>\n<')
                .replace(/}\s*(?=[a-zA-Z])/g, '}\n')
                .replace(/{/g, ' {\n')
                .replace(/;/g, ';\n')
                .split('\n');

            processed.forEach(function(line) {
                line = line.trim();
                if (line.length === 0) return;

                if (line.startsWith('}')) {
                    indentLevel = Math.max(0, indentLevel - 1);
                }

                if (line.startsWith('</')) {
                    indentLevel = Math.max(0, indentLevel - 1);
                }

                result.push(indent.repeat(indentLevel * indentSize) + line);

                if (line.endsWith('{')) {
                    indentLevel++;
                }

                if (line.startsWith('<') && !line.startsWith('</') && !line.startsWith('<!')) {
                    const tagMatch = line.match(/<(\w+)/);
                    if (tagMatch) {
                        const tagName = tagMatch[1].toLowerCase();
                        const isSelfClosing = selfClosingTags.includes(tagName) || line.endsWith('/>');

                        if (!isSelfClosing) {
                            indentLevel++;
                        }
                    }
                }
            });

            let formatted = result.join('\n').trim();
            formatted = formatted.replace(/\n\s*\n/g, '\n');

            editor.setValue(formatted);
            updateEditorStatus('Listo', 'circle-check', 'success');

            toastr.success('Código formateado correctamente', 'Éxito', {
                timeOut: 2000,
                progressBar: true
            });
        } catch (error) {
            console.error('Format error:', error);
            updateEditorStatus('Error', 'alert-circle', 'danger');
            toastr.error('Error al formatear el código', 'Error');
        }
    }

    // Listen to module change to update variables
    $('#module').on('change', function() {
        loadVariables();
    });

    // Initial load
    loadVariables();

    // Auto-update preview on change (only if preview tab is active)
    editor.on('change', function() {
        hasChanges = true;
        updateEditorStatus('Modificado', 'pencil', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function() {
            // Only update if preview tab is active
            if ($('#preview-tab').hasClass('active')) {
                updatePreview();
            }
            updateEditorStatus('Listo', 'circle-check', 'success');
        }, 2000);
    });

    // Button: Refresh Preview
    $('#btnRefreshPreviewCreate').on('click', function(e) {
        e.preventDefault();
        updatePreview();
        // Switch to preview tab
        $('#preview-tab').tab('show');
        $(this).prop('disabled', true);
        setTimeout(() => $(this).prop('disabled', false), 1000);
    });

    // Device view switcher (Create view)
    $('#preview-panel #btnDesktopViewCreate, #preview-panel #btnMobileViewCreate').on('click', function() {
        const width = $(this).data('width');
        const $container = $('#previewContainerTab');

        // Update active button - only within the preview panel
        $('#preview-panel .btn-group .btn').removeClass('active');
        $(this).addClass('active');

        // Animate container width
        $container.css('max-width', width);

        // Visual feedback
        if (width === '375px') {
            toastr.info('Vista móvil activada', 'Vista Previa', {
                timeOut: 1500,
                progressBar: true
            });
        } else {
            toastr.info('Vista desktop activada', 'Vista Previa', {
                timeOut: 1500,
                progressBar: true
            });
        }
    });

    // Button: Load Variables
    $('#btnLoadVariables').on('click', function(e) {
        e.preventDefault();
        loadVariables();
        toastr.info('Recargando variables...', 'Información');
    });

    // Button: Format Code
    $('#btnFormatCode').on('click', function(e) {
        e.preventDefault();
        formatCode();
    });

    // Button: Insert Variable from selector
    $('#btnInsertVariable').on('click', function(e) {
        e.preventDefault();
        const variableName = $('#variableSelector').val();

        if (!variableName) {
            toastr.warning('Por favor selecciona una variable', 'Atención', {
                timeOut: 2000
            });
            return;
        }

        insertVariable(variableName);

        // Reset selector
        $('#variableSelector').val('');
    });

    // Enter key on selector inserts variable
    $('#variableSelector').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#btnInsertVariable').click();
        }
    });

    // Change event on selector (optional: auto-insert on select)
    // Uncomment if you want auto-insert when selecting
    // $('#variableSelector').on('change', function() {
    //     const variableName = $(this).val();
    //     if (variableName) {
    //         insertVariable(variableName);
    //         $(this).val('');
    //     }
    // });

    // Ctrl+S to save
    editor.setOption('extraKeys', {
        'Ctrl-S': function(cm) {
            $('#formCreate').submit();
        },
        'Ctrl-/': 'toggleComment'
    });

    // Sync textarea before submit
    $('#formCreate').on('submit', function(e) {
        $('#content').val(editor.getValue());

        toastr.info('Creando plantilla...', 'Información', {
            timeOut: 0,
            extendedTimeOut: 0
        });
    });
});
</script>
@endpush

@endsection
