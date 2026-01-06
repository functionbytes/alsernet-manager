{{-- Componente: Tarjeta de Gestión del Documento --}}
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Gestión del documento</h5>
        <p class="small mb-0 text-muted">Indica si el documento fue procesado y cómo fue recibido</p>
    </div>
    <div class="card-body">
        <form id="formDocumentConfig" enctype="multipart/form-data" role="form" onSubmit="return false">
            {{ csrf_field() }}
            <input type="hidden" id="uid_config" name="uid" value="{{ $document->uid }}">

            <div class="row g-3">
                {{-- Campo: Estado del documento --}}
                @if(auth()->user()->canDocument('edit-status'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">Estado del documento</label>
                        <select class="form-select select2 select2" id="status_id" name="status_id">
                            <option value="">Selecciona un estado</option>
                            @forelse($statuses as $status)
                                <option value="{{ $status->id }}"
                                        data-key="{{ $status->key }}"
                                    {{ $document->status_id == $status->id ? 'selected' : '' }}>
                                    {{ $status->label }}
                                </option>
                            @empty
                                <option disabled>No hay estados disponibles</option>
                            @endforelse
                        </select>
                        <label id="status_id-error" class="error" for="status_id" style="display: none"></label>
                    </div>
                @endif

                {{-- Campo: Origen (canal) --}}
                @if(auth()->user()->canDocument('edit-source'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">Origen (canal)</label>
                        <select class="form-select select2 select2" id="source_id" name="source_id">
                            <option value="">Sin especificar</option>
                            @forelse($documentSources as $source)
                                <option value="{{ $source->id }}" {{ $document->source_id == $source->id ? 'selected' : '' }}>
                                    {{ $source->label }}
                                </option>
                            @empty
                                <option disabled>No hay orígenes disponibles</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- Campo: Método de carga --}}
                @if(auth()->user()->canDocument('edit-load'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">Método de carga</label>
                        <select class="form-select select2 select2" id="load_id" name="load_id">
                            <option value="">Sin especificar</option>
                            @forelse($documentLoads as $load)
                                <option value="{{ $load->id }}" {{ $document->load_id == $load->id ? 'selected' : '' }}>
                                    {{ $load->label }}
                                </option>
                            @empty
                                <option disabled>No hay métodos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- Campo: Tipo de sincronización --}}
                @if(auth()->user()->canDocument('edit-sync'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo de sincronización</label>
                        <select class="form-select select2 select2" id="sync_id" name="sync_id">
                            <option value="">Sin especificar</option>
                            @forelse($documentSyncs as $sync)
                                <option value="{{ $sync->id }}" {{ $document->sync_id == $sync->id ? 'selected' : '' }}>
                                    {{ $sync->label }}
                                </option>
                            @empty
                                <option disabled>No hay tipos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- Campo: Tipo de subida --}}
                @if(auth()->user()->canDocument('edit-upload'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo de subida</label>
                        <select class="form-select select2 select2" id="upload_id" name="upload_id">
                            <option value="">Sin especificar</option>
                            @forelse($uploadTypes as $uploadType)
                                <option value="{{ $uploadType->id }}" {{ $document->upload_id == $uploadType->id ? 'selected' : '' }}>
                                    {{ $uploadType->label }}
                                </option>
                            @empty
                                <option disabled>No hay tipos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- Campo: Requiere financiación (Cambiado a select) --}}
                @if(auth()->user()->canDocument('edit-financing'))
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Requiere financiación
                        </label>
                        <select class="form-select" id="requires_financing" name="requires_financing">
                            <option value="0" {{ !$document->requires_financing ? 'selected' : '' }}>
                                No requiere financiación
                            </option>
                            <option value="1" {{ $document->requires_financing ? 'selected' : '' }}>
                                Sí requiere financiación
                            </option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Si se selecciona "Sí requiere financiación", el documento pasará por validación de contabilidad.
                            Esto define el número de etapas del workflow de validación.
                        </small>
                    </div>
                @endif

                {{-- Botón de guardar (se muestra si al menos un campo es editable) --}}
                @if(auth()->user()->canDocument('edit-status') ||
                    auth()->user()->canDocument('edit-source') ||
                    auth()->user()->canDocument('edit-load') ||
                    auth()->user()->canDocument('edit-sync') ||
                    auth()->user()->canDocument('edit-upload') ||
                    auth()->user()->canDocument('edit-financing'))
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar configuración
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Modal de confirmación --}}
@include('documents::documents.documents.components.management.modals.confirm-configuration')
