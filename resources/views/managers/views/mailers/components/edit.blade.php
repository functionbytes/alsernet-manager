@extends('managers.includes.layout')

@section('page_title', 'Editar Componente: ' . $component->subject)

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb Card --}}
    @include('managers.includes.card', [
        'title' => 'Editor de Componente de Email',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/home')],
            ['label' => 'Configuración', 'url' => route('manager.settings')],
            ['label' => 'Componentes', 'url' => route('manager.settings.mailers.components.index')],
            ['label' => $component->subject, 'active' => true]
        ]
    ])

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa fa-exclamation-circle fs-4 me-2 mt-1"></i>
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
    <form method="POST" action="{{ route('manager.settings.mailers.components.update', $component->uid) }}" id="formEdit">
        @csrf
        @method('PATCH')
        <input type="hidden" name="lang_id" value="{{ $currentLangId ?? 1 }}">
        <input type="hidden" name="translation_uid" value="{{ $translation->uid ?? '' }}">

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
                                    <small class="text-muted">Edita el contenido del componente</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge  text-info ">
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
                        <div class="d-flex gap-3 align-items-center justify-content-between">
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
                                <button type="button" class="btn btn-secondary" id="btnToggleVariables"
                                        data-bs-toggle="tooltip" title="Mostrar panel de variables">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                            </div>

                            <!-- Auto-save Indicator -->
                            <div class="d-flex gap-2 align-items-center ms-auto">
                                <small class="text-muted d-none d-md-inline">
                                    <i class="fas fa-circle-check text-success me-1"></i>Se guarda automáticamente
                                </small>
                                <div id="autoSaveIndicator" class="d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Guardando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Component Info --}}
                    <div class="card-body border-bottom">
                        <div class="row g-3">
                            <div class="col-12 col-md-12">
                                <label for="subject" class="form-label fw-semibold">
                                    Nombre del componente
                                </label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                       id="subject" name="subject" value="{{ old('subject', $translation->subject ?? '') }}"
                                       placeholder="Ej: Header principal, Footer de emails..." required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-12">
                                <label for="type" class="form-label fw-semibold">
                                    Tipo
                                </label>
                                <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="partial" @if($component->type === 'partial') selected @endif>Parcial</option>
                                    <option value="layout" @if($component->type === 'layout') selected @endif>Layout</option>
                                    <option value="component" @if($component->type === 'component') selected @endif>Componente</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="current_lang_display" class="form-label fw-semibold">
                                    Idioma actual
                                </label>
                                <input type="text" class="form-control" id="current_lang_display"
                                       value="{{ $translation->lang?->title ?? 'No definido' }}" disabled readonly>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Ver otras traducciones abajo
                                </small>
                            </div>

                            <div class="col-12 col-md-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_protected" name="is_protected" value="1" @if(old('is_protected', $component->is_protected)) checked @endif>
                                    <label class="form-check-label" for="is_protected">
                                        <strong>Componente protegido</strong>
                                        <small class="d-block text-muted">Si activas esta opción, el componente no podrá ser eliminado sin desactivar primero la protección</small>
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="alert alert-info border-0 mb-0 mt-3 d-flex align-items-start">
                            <i class="fas fa-info-circle fs-5 me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span><strong>Alias:</strong> <code class="text-primary">{{ $component->alias }}</code></span>
                                    <span class="text-muted">•</span>
                                    <span><strong>Código:</strong> <code class="text-primary">{{ $component->code }}</code></span>
                                    @if (in_array($component->alias, ['email_template_header', 'email_template_footer', 'email_template_wrapper']))
                                        <span class="badge bg-warning-subtle text-warning">
                                            Componente del sistema
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabs Navigation --}}
                    <ul class="nav nav-tabs nav-fill border-bottom" id="editorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="code-tab" data-bs-toggle="tab" data-bs-target="#code-panel" type="button" role="tab" aria-controls="code-panel" aria-selected="true">
                                Código
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preview-tab" data-bs-toggle="tab"  data-bs-target="#preview-panel" type="button" role="tab" aria-controls="preview-panel" aria-selected="false">
                                Vista
                            </button>
                        </li>
                    </ul>

                    {{-- Tabs Content --}}
                    <div class="tab-content" id="editorTabsContent">

                        {{-- Tab 1: Code Editor --}}
                        <div class="tab-pane fade show active p-0" id="code-panel" role="tabpanel" aria-labelledby="code-tab">

                            {{-- Variables Panel Below Editor --}}
                            <div class="border-top p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
                                    <div>
                                        <h6 class="mb-1 fw-semibold text-dark">
                                            Variables disponibles
                                        </h6>
                                        <small class="text-muted d-block">
                                            Haz clic en cualquier variable para insertarla en el editor
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnLoadVariables" data-bs-toggle="tooltip" aria-label="Recargar variables" data-bs-original-title="Recargar variables">
                                        <i class="fas fa-sync-alt me-1"></i>
                                    </button>
                                </div>
                                <div id="variablesPanel" style="max-height: 350px; overflow-y: auto;">
                                    <div class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm mb-2" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mb-0 small">Cargando variables...</p>
                                    </div>
                                </div>
                            </div>

                            <textarea class="form-control" id="content" name="content" style="display: none;">{{ old('content', $translation->content ?? '') }}</textarea>

                        </div>

                        {{-- Tab 2: Preview Panel --}}
                        <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel" aria-labelledby="preview-tab">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                                <div>
                                    <h6 class="mb-1 fw-semibold text-dark">
                                        Vista previa del email
                                    </h6>
                                    <small class="text-muted d-block">
                                        Cambia entre vistas de escritorio y móvil para ver cómo se verá tu email
                                    </small>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Device preview">
                                        <button type="button" class="btn btn-outline-primary active" id="btnDesktopViewEdit" data-width="100%" data-bs-toggle="tooltip" aria-label="Vista Desktop (100%)" data-bs-original-title="Vista Desktop (100%)">
                                            <i class="fas fa-desktop me-1"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="btnMobileViewEdit" data-width="375px" data-bs-toggle="tooltip" aria-label="Vista Mobile (375px)" data-bs-original-title="Vista Mobile (375px)">
                                            <i class="fas fa-mobile-screen me-1"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnRefreshPreviewEdit" data-bs-toggle="tooltip" aria-label="Actualizar vista previa" data-bs-original-title="Actualizar vista previa">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="previewContainerTab" style="min-height: 500px; max-height: 700px; overflow-y: auto; background: #f8f9fa; border-radius: 4px;">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="text-muted mb-0">Cargando vista previa...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="card-footer bg-white border-top">
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            Guardar
                        </button>

                        <a href="{{ route('manager.settings.mailers.components.preview', ['uid' => $component->uid, 'lang_id' => $currentLangId ?? 1]) }}" class="btn btn-info w-100 mb-1" target="_blank">
                            Vista previa
                        </a>

                        <a href="{{ route('manager.settings.mailers.components.index') }}" class="btn btn-secondary w-100">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Preview & Variables --}}
            <div class="col-12 col-lg-4">
                {{-- Traducciones / Idiomas --}}
                <div class="card mb-3">
                    <div class="card-header bg-warning-subtle border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    Traducciones por idioma
                                </h6>
                                <small class="text-muted">Estado de completitud de cada idioma</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            {{-- Idioma Actual (Siendo Editado) --}}
                            <div class="list-group-item active bg-primary-subtle border-primary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong class="text-black">{{ $translation->lang?->title ?? 'Sin idioma' }}</strong>
                                                <span class="badge bg-primary">
                                                    Editando
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                @if ($translation->subject && $translation->content)
                                                    Completa
                                                @else
                                                    Incompleta
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Otros Idiomas Disponibles --}}
                            @if (!empty($langs) && $langs->count() > 1)
                                @foreach ($langs as $lang)
                                    @if ($lang->id !== $currentLangId)
                                        @php
                                            $langTranslation = $component->translations()->where('lang_id', $lang->id)->first();
                                            $isComplete = $langTranslation && !empty($langTranslation->subject) && !empty($langTranslation->content);
                                            $translationUid = $langTranslation?->uid;
                                            $statusBadge = $isComplete
                                                ? ['class' => 'bg-success-subtle text-success',  'text' => 'Completa']
                                                : ['class' => 'bg-warning-subtle text-warning', 'text' => 'Pendiente'];
                                        @endphp
                                        <a href="{{ route('manager.settings.mailers.components.edit', ['uid' => $component->uid, 'translation_uid' => $translationUid]) }}"
                                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-3 hover-shadow-sm"
                                           data-bs-toggle="tooltip" title="Cambiar a {{ $lang->title }}">
                                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <strong>{{ $lang->title }}</strong>
                                                        <span class="badge {{ $statusBadge['class'] }}">
                                                            {{ $statusBadge['text'] }}
                                                        </span>
                                                    </div>
                                                    @if ($langTranslation)
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>Actualizado: {{ $langTranslation->updated_at->diffForHumans() }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-plus-circle me-1"></i>No creada aún
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right text-muted flex-shrink-0"></i>
                                        </a>
                                    @endif
                                @endforeach
                            @else
                                <div class="list-group-item text-center py-4">
                                    <i class="fas fa-language fs-1 text-muted d-block mb-2"></i>
                                    <small class="text-muted">Solo hay un idioma disponible</small>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Footer con resumen --}}
                    <div class="card-footer bg-light p-2 small text-muted">
                        <div class="d-flex gap-3 flex-wrap">
                            <span>
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <strong>{{ $component->translations->where('subject', '!=', null)->where('content', '!=', null)->count() }}</strong> completas
                            </span>
                            <span>
                                <i class="fas fa-exclamation-circle text-warning me-1"></i>
                                <strong>{{ $langs->count() - $component->translations->where('subject', '!=', null)->where('content', '!=', null)->count() }}</strong> pendientes
                            </span>
                        </div>
                    </div>
                </div>


                {{-- Keyboard Shortcuts --}}
                <div class="card">
                    <div class="card-header  border-bottom p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Atajos de teclado</h6>
                                <small class="text-muted">Acelera tu trabajo con estos atajos</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Guardar plantilla</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+S</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Autocompletar</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+Space</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Comentar/Descomentar</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+/</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Expandir Emmet</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Tab</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Envolver con Emmet</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+Alt+Enter</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-2 text-center">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            Usa <strong>Emmet</strong> para escribir HTML más rápido
                        </small>
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
        transition: all 0.2s ease;
    }

    .variable-insert {
        transition: all 0.2s ease;
        cursor: pointer;
        border-left: 3px solid transparent;
    }

    .variable-insert:hover {
        background: #f6f7f9 !important;
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
        align-items: flex-start;
        background: #ffffff;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px transparent;
    }

    #previewContainer iframe {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
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
    #btnRefreshPreviewEdit {
        transition: all 0.2s ease;
    }

    #btnRefreshPreviewEdit:hover:not(:disabled) {
        box-shadow: 0 2px 8px rgba(19, 198, 114, 0.3);
        transform: translateY(-1px);
    }

    #btnRefreshPreviewEdit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Variables Panel Styling */
    #btnLoadVariables {
        transition: all 0.2s ease;
    }

    #btnLoadVariables:hover {
        box-shadow: 0 2px 8px rgba(19, 198, 114, 0.3);
        transform: translateY(-1px);
    }

    #btnLoadVariables:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    #variablesPanel {
        transition: all 0.3s ease;
        overflow-x: hidden;
    }

    .variable-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .variable-card:hover {
        transform: translateY(-2px);
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

    /* Keyboard Shortcuts Card */
    kbd {
        font-family: 'Courier New', monospace;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15), inset 0 -2px 0 rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(0, 0, 0, 0.2);
        display: inline-block;
        line-height: 1;
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

    @media (max-width: 991px) {
        .CodeMirror {
            height: 400px !important;
        }

        .variable-card {
            min-height: 60px;
        }

        .variable-code {
            font-size: 0.75rem;
        }
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

<!-- CodeMirror Autocomplete (Hint) -->
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/html-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/css-hint.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.css">

<!-- Emmet for CodeMirror -->
<script src="https://cdn.jsdelivr.net/npm/emmet-codemirror@1.1.106/emmet.min.js"></script>

<!-- HTML/CSS/JS Beautifier -->
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify-html.js"></script>

<script>
// Prevent Quill from initializing on this page (we use CodeMirror instead)
console.log('🎨 CodeMirror Email Editor Loading...');

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

    // Verify textarea exists before initializing CodeMirror
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

    // Initialize Emmet for CodeMirror
    try {
        if (typeof emmetCodeMirror !== 'undefined') {
            emmetCodeMirror(editor);
            console.log('✅ Emmet initialized successfully!');
        }
    } catch (e) {
        console.warn('⚠️ Emmet not available:', e);
    }

    // Smart Autocomplete - Complete system for email templates
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
            // 🎨 CSS Properties - Complete for emails
            const cssProperties = [
                // Colors & Background
                'color', 'background-color', 'background', 'opacity',
                // Dimensions
                'width', 'height', 'max-width', 'min-width', 'min-height', 'max-height',
                // Spacing
                'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
                'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
                // Borders
                'border', 'border-color', 'border-radius', 'border-width', 'border-style',
                'border-top', 'border-bottom', 'border-left', 'border-right',
                // Typography
                'font-size', 'font-weight', 'font-family', 'font-style', 'line-height',
                'text-align', 'text-decoration', 'text-transform', 'letter-spacing',
                'text-indent', 'white-space', 'word-break', 'word-wrap',
                // Layout
                'display', 'position', 'overflow', 'overflow-x', 'overflow-y',
                'visibility', 'z-index', 'float', 'clear',
                // Flexbox
                'flex', 'flex-direction', 'flex-wrap', 'flex-grow', 'flex-shrink',
                'justify-content', 'align-items', 'align-content', 'gap',
                // Effects
                'box-shadow', 'text-shadow', 'transform', 'transition', 'animation'
            ];

            completions = cssProperties
                .filter(prop => prop.toLowerCase().startsWith(word))
                .map(prop => ({text: prop + ': ', displayText: `🎨 ${prop}`}));
        }
        else if (insideHref) {
            // 🔗 URLs & Links
            const urls = [
                '{SITE_URL}', '{RESET_LINK}', 'https://', 'http://', 'mailto:', 'tel:',
                '#', 'javascript:void(0)'
            ];

            completions = urls
                .filter(url => url.toLowerCase().startsWith(word))
                .map(url => ({text: url, displayText: `🔗 ${url}`}));
        }
        else if (insideSrc) {
            // 🖼️ Image URLs & Variables
            const images = [
                '{LOGO_URL}', 'https://', 'http://', 'data:image/png;base64,'
            ];

            completions = images
                .filter(img => img.toLowerCase().startsWith(word))
                .map(img => ({text: img, displayText: `🖼️ ${img}`}));
        }
        else if (insideAlt) {
            // 📝 Common alt text
            const alts = [
                'Logo', 'Banner', 'Product Image', 'Company Logo', 'Hero Image', 'Icon',
                'Button Image', 'Social Icon', 'Header Image', 'Footer Image'
            ];

            completions = alts
                .filter(alt => alt.toLowerCase().startsWith(word))
                .map(alt => ({text: alt, displayText: `📝 ${alt}`}));
        }
        else if (afterTagName) {
            // 🏷️ HTML Attributes - Email focused
            const htmlAttributes = [
                // Basic
                'id', 'class', 'style', 'dir',
                // Links & Media
                'href', 'src', 'alt', 'title', 'target',
                // Form
                'name', 'value', 'type', 'placeholder', 'required', 'disabled', 'readonly',
                // Table (Email common)
                'cellpadding', 'cellspacing', 'border', 'bordercolor', 'align', 'valign',
                'colspan', 'rowspan', 'bgcolor', 'width', 'height',
                // Email specific
                'role', 'aria-label', 'aria-describedby',
                // Events
                'onclick', 'onload', 'onmouseover', 'onmouseout',
                // Others
                'action', 'method', 'enctype', 'tabindex', 'lang'
            ];

            completions = htmlAttributes
                .filter(attr => attr.toLowerCase().startsWith(word))
                .map(attr => ({text: attr + '="', displayText: `🏷️ ${attr}`}));
        }
        else if (insideTag) {
            // 📦 Classes - Bootstrap & Custom
            const classes = [
                // Email container classes
                'email-wrapper', 'email-container', 'email-body', 'email-footer',
                'email-header', 'email-content', 'email-section',
                // Bootstrap Layout
                'container', 'container-fluid', 'row', 'col', 'col-12', 'col-6', 'col-4', 'col-3',
                'col-md-6', 'col-lg-6', 'col-xl-6',
                // Spacing
                'mt-1', 'mt-2', 'mt-3', 'mt-4', 'mt-5', 'mb-1', 'mb-2', 'mb-3', 'mb-4', 'mb-5',
                'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'px-2', 'py-2', 'pt-2', 'pb-2',
                'ms-1', 'me-1', 'ms-auto',
                // Display & Flex
                'd-flex', 'd-block', 'd-none', 'd-inline', 'd-grid',
                'flex-column', 'flex-row', 'justify-content-center', 'justify-content-between',
                'justify-content-end', 'align-items-center', 'align-items-start', 'gap-2', 'gap-3',
                // Colors
                'text-primary', 'text-success', 'text-danger', 'text-warning', 'text-muted',
                'bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-light',
                // Typography
                'text-center', 'text-end', 'text-start', 'fw-bold', 'fw-normal', 'small', 'lead',
                // Buttons
                'btn', 'btn-primary', 'btn-secondary', 'btn-success', 'btn-danger',
                'btn-outline-primary', 'btn-lg', 'btn-sm',
                // Cards & Sections
                'card', 'card-body', 'card-header', 'card-footer', 'card-title', 'section-block',
                // Tables
                'table', 'table-striped', 'table-hover', 'table-bordered', 'table-responsive',
                // Forms
                'form-control', 'form-group', 'form-label', 'input-group',
                // Alerts
                'alert', 'alert-primary', 'alert-success', 'alert-danger', 'alert-warning',
                // Utilities
                'rounded', 'shadow', 'border', 'h-100', 'w-100', 'overflow-hidden',
                'text-truncate', 'text-uppercase', 'text-lowercase'
            ];

            completions = classes
                .filter(cls => cls.toLowerCase().startsWith(word))
                .map(cls => ({text: cls, displayText: `📦 ${cls}`}));
        }
        else {
            // 🏷️ HTML Tags - Email template focused
            const htmlTags = [
                // Email structure
                'html', 'head', 'body', 'meta', 'title',
                // Containers
                'div', 'section', 'header', 'footer', 'main', 'article',
                // Tables (Email common)
                'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
                // Content
                'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'span', 'strong', 'em', 'u', 'code', 'pre', 'blockquote',
                // Lists
                'ul', 'ol', 'li', 'dl', 'dt', 'dd',
                // Media
                'img', 'picture', 'figure', 'figcaption',
                // Links & Navigation
                'a', 'nav', 'menu',
                // Forms
                'form', 'input', 'button', 'label', 'select', 'textarea', 'fieldset',
                // Text
                'br', 'hr', 'small', 'mark', 'del', 'ins', 'sub', 'sup',
                // Semantic
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

    // 🔤 Template Variables Hint
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
            // Site
            'SITE_NAME', 'SITE_URL', 'SITE_EMAIL', 'LOGO_URL',
            // Company
            'COMPANY_NAME', 'COMPANY_ADDRESS', 'COMPANY_CITY', 'COMPANY_STATE', 'COMPANY_ZIP',
            'COMPANY_COUNTRY', 'COMPANY_PHONE', 'COMPANY_EMAIL', 'COMPANY_WEBSITE',
            // Dates
            'CURRENT_YEAR', 'CURRENT_MONTH', 'CURRENT_DAY', 'CURRENT_DATE', 'CURRENT_TIME',
            // Recipient
            'RECIPIENT_EMAIL', 'CUSTOMER_NAME', 'CUSTOMER_FIRST_NAME', 'CUSTOMER_LAST_NAME',
            'CUSTOMER_PHONE', 'CUSTOMER_ADDRESS',
            // Email
            'EMAIL_SUBJECT', 'EMAIL_TITLE', 'CONTENT', 'FOOTER_CONTENT',
            // Links
            'RESET_LINK', 'CONFIRM_LINK', 'ACTIVATION_LINK', 'UNSUBSCRIBE_LINK',
            // Special
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

    const componentUid = '{{ $component->uid }}';
    const currentLangId = {{ $currentLangId ?? 1 }};
    let isPreviewExpanded = false;
    let previewTimeout;
    let hasChanges = false;

    // Update Editor Status
    function updateEditorStatus(status, icon = 'check-circle', color = 'success') {
        $('#editorStatus')
            .html(`<i class="fas fa-${icon} me-1"></i>${status}`)
            .attr('class', `badge bg-${color}`);
    }

    // Update Preview Status
    function updatePreviewStatus(status, icon = 'circle', color = 'success') {
        $('#previewStatus')
            .text(status)
            .attr('class', 'badge bg-primary');
    }

    // Update Preview (both containers)
    function updatePreview() {
        const previewUrl = `{{ route('manager.settings.mailers.components.preview-ajax', $component->uid) }}?lang_id=${currentLangId}`;

        $.ajax({
            url: previewUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Update right panel preview
                    const $container = $('#previewContainer');
                    const $containerTab = $('#previewContainerTab');
                    const height = isPreviewExpanded ? '800px' : '400px';

                    const $iframe = $('<iframe>')
                        .css({
                            'width': '100%',
                            'min-height': height,
                            'border': 'none',
                            'display': 'block',
                            'background': 'white'
                        });

                    const $iframeTab = $('<iframe>')
                        .css({
                            'width': '100%',
                            'border': 'none',
                            'display': 'block',
                            'background': 'white',
                            'min-height': '500px'
                        });

                    $container.empty().append($iframe);
                    $containerTab.empty().append($iframeTab);

                    $iframe[0].srcdoc = data.html;
                    $iframeTab[0].srcdoc = data.html;

                    updatePreviewStatus('En vivo', 'circle-dot-filled', 'success');
                }
            },
            error: function(error) {
                console.error('Error updating preview:', error);
                updatePreviewStatus('Error', 'alert-circle', 'danger');
                const errorHtml = '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar vista previa</div>';
                $('#previewContainer').html(errorHtml);
                $('#previewContainerTab').html(errorHtml);
            }
        });
    }

    // Load Variables
    function loadVariables() {
        const variablesUrl = '{{ route('manager.settings.mailers.components.variables') }}';

        $.ajax({
            url: variablesUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    renderVariables(data.variables);
                }
            },
            error: function(error) {
                console.error('Error loading variables:', error);
                $('#variablesPanel').html(
                    '<div class="alert alert-danger m-2"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar variables</div>'
                );
            }
        });
    }

    // Render Variables
    function renderVariables(variableGroups) {
        let html = '<div class="row g-1">';

        $.each(variableGroups, function(idx, group) {
            $.each(group.items, function(idx, variable) {
                html += `<div class="col-6 col-md-4 col-lg-3">`;
                html += `<div class="variable-card variable-insert" data-variable-name="${variable.name}" data-bs-toggle="tooltip" title="${variable.description}">`;
                html += `<code class="variable-code">{${variable.name}}</code>`;
                html += `</div>`;
                html += `</div>`;
            });
        });

        html += '</div>';

        $('#variablesPanel').html(html);

        // Initialize tooltips for new elements
        $('[data-bs-toggle="tooltip"]').each(function() {
            new bootstrap.Tooltip(this);
        });

        // Add event listeners with jQuery
        $(document).on('click', '.variable-insert', function(e) {
            e.preventDefault();
            const variableName = $(this).data('variable-name');
            const variable = `{${variableName}}`;
            const cursor = editor.getCursor();
            editor.replaceRange(variable, cursor);
            editor.focus();

            // Show feedback
            toastr.success(`Variable ${variable} insertada`, 'Éxito', {
                timeOut: 2000,
                progressBar: true
            });
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

    // Toggle Preview Size
    function togglePreview() {
        isPreviewExpanded = !isPreviewExpanded;
        const $btn = $('#btnTogglePreview');
        const $container = $('#previewContainer');

        if (isPreviewExpanded) {
            $container.css('max-height', '1200px');
            $btn.html('<i class="fas fa-compress"></i>');
        } else {
            $container.css('max-height', '600px');
            $btn.html('<i class="fas fa-expand"></i>');
        }

        updatePreview();
    }

    // Toggle Variables Panel (Mobile)
    function toggleVariables() {
        $('#variablesCard').toggleClass('d-none');
    }

    // Initial load
    updatePreview();
    loadVariables();

    // Auto-update preview on change
    editor.on('change', function() {
        hasChanges = true;
        updateEditorStatus('Modificado', 'pencil', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function() {
            updatePreview();
            updateEditorStatus('Listo', 'circle-check', 'success');
        }, 2000);
    });

    // Button: Refresh Preview (Toolbar)
    $('#btnRefreshPreview').on('click', function(e) {
        e.preventDefault();
        updatePreview();
    });

    // Button: Refresh Preview (Tab)
    $('#btnRefreshPreviewTab').on('click', function(e) {
        e.preventDefault();
        updatePreview();
        $(this).prop('disabled', true);
        setTimeout(() => $(this).prop('disabled', false), 1000);
    });

    // Tab: Update preview when preview tab is shown
    $('#preview-tab').on('shown.bs.tab', function (e) {
        updatePreview();
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

    // Button: Toggle Preview
    $('#btnTogglePreview').on('click', function(e) {
        e.preventDefault();
        togglePreview();
    });

    // Button: Toggle Variables (Mobile)
    $('#btnToggleVariables').on('click', function(e) {
        e.preventDefault();
        toggleVariables();
    });

    // Button: Desktop View (Preview Tab)
    $('#btnDesktopViewEdit').on('click', function(e) {
        e.preventDefault();
        $('#previewContainerTab').css('width', '100%');
        $('#btnDesktopViewEdit').addClass('active');
        $('#btnMobileViewEdit').removeClass('active');
    });

    // Button: Mobile View (Preview Tab)
    $('#btnMobileViewEdit').on('click', function(e) {
        e.preventDefault();
        $('#previewContainerTab').css('width', '375px');
        $('#btnMobileViewEdit').addClass('active');
        $('#btnDesktopViewEdit').removeClass('active');
    });

    // Button: Refresh Preview (Edit Tab)
    $('#btnRefreshPreviewEdit').on('click', function(e) {
        e.preventDefault();
        updatePreview();
        $(this).prop('disabled', true);
        setTimeout(() => $(this).prop('disabled', false), 1000);
    });

    // Ctrl+S to save
    editor.setOption('extraKeys', {
        'Ctrl-S': function(cm) {
            $('#formEdit').submit();
        },
        'Ctrl-/': 'toggleComment'
    });

    // Track original form state
    const originalFormState = {
        subject: $('#subject').val(),
        content: editor.getValue(),
        type: $('#type').val(),
        is_protected: $('#is_protected').is(':checked')
    };

    function checkForChanges() {
        const currentState = {
            subject: $('#subject').val(),
            content: editor.getValue(),
            type: $('#type').val(),
            is_protected: $('#is_protected').is(':checked')
        };

        const hasChanges =
            originalFormState.subject !== currentState.subject ||
            originalFormState.content !== currentState.content ||
            originalFormState.type !== currentState.type ||
            originalFormState.is_protected !== currentState.is_protected;

        return hasChanges;
    }

    function updateSubmitButtonState() {
        const $submitBtn = $('#formEdit button[type="submit"]');
        const hasChanges = checkForChanges();

        if (hasChanges) {
            $submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
            updateEditorStatus('Hay cambios sin guardar', 'exclamation-circle', 'warning');
        } else {
            $submitBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
            if (!hasChanges && !editor.getValue()) {
                updateEditorStatus('Listo', 'check-circle', 'success');
            }
        }
    }

    // Monitor form changes
    $('#subject, #type, #is_protected').on('change input', updateSubmitButtonState);
    editor.on('change', updateSubmitButtonState);

    // Initial state - disable submit button
    updateSubmitButtonState();

    // Sync textarea before submit
    $('#formEdit').on('submit', function(e) {
        $('#content').val(editor.getValue());

        const hasChanges = checkForChanges();
        if (!hasChanges) {
            e.preventDefault();
            alert('No hay cambios para guardar. Por favor, modifica al menos un campo.');
            return false;
        }

        // Show saving indicator
        toastr.info('Guardando cambios...', 'Información', {
            timeOut: 0,
            extendedTimeOut: 0
        });
    });
});
</script>
@endpush

@endsection
