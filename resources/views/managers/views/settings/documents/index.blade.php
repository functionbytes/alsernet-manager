@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Configuración de Documentos'])


        @if ($message = session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-checkme-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($message = session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-circle-exclamation me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3">

            <!-- Opción 1: Configuración Global -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-dark">
                                Configuración global
                            </h6>
                            <span class="badge bg-black">Global</span>
                        </div>
                    </div>

                    <div class="card-body  pb-0">
                        <p class="text-muted mb-3">
                            Configura el comportamiento general del sistema de documentos que se aplica a <strong>TODOS</strong>
                            los tipos de documento.
                        </p>

                        <div class="alert alert-info alert-sm py-2 px-3 mb-3" role="alert">
                            <strong>¿Qué configuras aquí?</strong>
                        </div>

                        <ul class="list-unstyled ms-3 mb-4">
                            <li class="mb-2">
                                <strong>Solicitud inicial</strong>
                                <br>
                                <small class="text-muted">Habilitar/deshabilitar solicitud de documentos cuando se crea una orden. Incluye mensaje personalizado.</small>
                            </li>
                            <li class="mb-2">
                                <strong>Recordatorios automáticos</strong>
                                <br>
                                <small class="text-muted">Enviar recordatorios automáticos después de X días. Especifica intervalo y mensaje personalizado.</small>
                            </li>
                            <li class="mb-2">
                                <strong>Documentos específicos</strong>
                                <br>
                                <small class="text-muted">Permitir solicitar documentos específicos que los clientes deben re-cargar o corregir.</small>
                            </li>
                        </ul>

                        <p class="text-muted small border-top pt-3">
                            Estas configuraciones se aplican automáticamente a todos los tipos de documento sin excepción.
                        </p>
                    </div>

                    <div class="card-footer  border-top">
                        <a href="{{ route('manager.settings.documents.configurations.global') }}" class="btn btn-primary w-100">
                            <i class="fa fa-arrow-right me-2"></i> Ir a configuración global
                        </a>
                    </div>
                </div>
            </div>

            <!-- Opción 2: Tipos de Documentos -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-dark">
                                Tipos de documentos
                            </h6>
                            <span class="badge bg-light-secondary">Específico</span>
                        </div>
                    </div>

                    <div class="card-body pb-0">
                        <p class="text-muted mb-3">
                            Configura los <strong>documentos específicos requeridos</strong> para cada tipo de solicitud
                            (Armas Cortas, Rifles, Escopetas, etc.). Crea nuevos tipos personalizados si lo necesitas.
                        </p>

                        <div class="alert alert-info alert-sm py-2 px-3 mb-3" role="alert">
                            <strong>¿Qué configuras aquí?</strong>
                        </div>

                        <ul class="list-unstyled ms-3 mb-4">
                            <li class="mb-2">
                                <strong>Documentos por tipo</strong>
                                <br>
                                <small class="text-muted">Define qué documentos se requieren para cada tipo (DNI, Licencia, etc.).</small>
                            </li>
                            <li class="mb-2">
                                <strong>Crear tipos personalizados</strong>
                                <br>
                                <small class="text-muted">Crea nuevos tipos de documento con sus propios requisitos únicos.</small>
                            </li>
                            <li class="mb-2">
                                <strong>Editar configuraciones</strong>
                                <br>
                                <small class="text-muted">Modifica los documentos requeridos para cualquier tipo en cualquier momento.</small>
                            </li>
                        </ul>

                        <p class="text-muted small border-top pt-3">
                            Cada tipo tiene sus propios requisitos independientes. Los clientes verán solo los documentos
                            necesarios para su tipo específico.
                        </p>
                    </div>

                    <div class="card-footer  border-top">
                        <a href="{{ route('manager.settings.documents.types') }}" class="btn btn-primary w-100">
                            <i class="fa fa-arrow-right me-2"></i> Ir a tipos de documentos
                        </a>
                    </div>
                </div>
            </div>

        </div>


@endsection
