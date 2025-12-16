@extends('mailers.documents.layouts.document')

@section('title', 'Documentación pendiente para tu pedido')
@section('header', 'Documentación pendiente')
@section('header-subtitle', 'Acción requerida')

@section('content')
    <div class="greeting">
        ¡Hola {{ $customerName ?? 'cliente' }}!
    </div>

    <p>Hemos revisado la documentación para tu pedido @if($orderReference)# {{ $orderReference }}@endif y hemos notado que falta información o algunos documentos no son legibles.</p>

    <div class="alert alert-danger">
        <p><strong>IMPORTANTE:</strong> Necesitamos que nos envíes o reenvíes los siguientes documentos para poder procesar tu pedido:</p>
        <div class="missing-list" style="background-color: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-top: 10px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($missingDocs as $docKey)
                    @php
                        $label = '';
                        switch($docKey) {
                            case 'dni_frontal': $label = 'DNI - Cara delantera'; break;
                            case 'dni_trasera': $label = 'DNI - Cara trasera'; break;
                            case 'licencia': $label = 'Licencia de armas'; break;
                            case 'dni': $label = 'DNI (ambas caras)'; break;
                            case 'documento': $label = 'Documento de identidad'; break;
                            default: $label = ucfirst(str_replace('_', ' ', $docKey));
                        }
                    @endphp
                    <li style="margin-bottom: 5px; color: #dc2626; font-weight: bold;">{{ $label }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    @if($notes)
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <p style="margin: 0; font-weight: bold; color: #374151;">Nota adicional:</p>
            <p style="margin-top: 5px; font-style: italic;">"{{ $notes }}"</p>
        </div>
    @endif

    <p><strong>Por favor, sube los documentos faltantes haciendo clic en el siguiente botón:</strong></p>

    @if($uploadUrl)
        <div class="button-group">
            <a href="{{ $uploadUrl }}" class="button" target="_blank" rel="noopener">Subir documentos faltantes</a>
        </div>
    @else
        <p>Puedes responder a este correo adjuntando los archivos solicitados.</p>
    @endif

    <p><strong>¿Tienes dudas?</strong> Responde a este correo y te ayudaremos.</p>

    <p>Saludos,<br/>
    <strong>El equipo de Soporte</strong></p>
@endsection
