@extends('mailers.documents.layouts.document')

@section('title', 'Sube la documentación para tu pedido')
@section('header', 'Sube tu documentación')
@section('header-subtitle', 'Completa tu pedido')

@section('content')
    <div class="greeting">
        ¡Hola {{ $customerName ?? 'cliente' }}!
    </div>

    <p>Gracias por tu compra. Para completar tu pedido @if($orderReference) # {{ $orderReference }} @endif, necesitamos que cargues la documentación solicitada en los próximos días.</p>

    @php
        $documentType = $documentType ?? 'general';
    @endphp

    @if($documentType === 'corta')
        <div class="alert alert-warning">
            <p><strong>RECUERDA:</strong> Para poder enviar tu arma de fuego, necesitamos que nos envíes la siguiente documentación:</p>
            <ul style="padding-left: 20px; margin: 8px 0;">
                <li>Fotocopia de tu DNI (ambas caras)</li>
                <li>Fotocopia de tu licencia de armas cortas (tipo B) o licencia de tiro olímpico (tipo F)</li>
            </ul>
        </div>
    @elseif($documentType === 'rifle')
        <div class="alert alert-warning">
            <p><strong>RECUERDA:</strong> Para poder enviar tu arma de fuego, necesitamos que nos envíes la siguiente documentación:</p>
            <ul style="padding-left: 20px; margin: 8px 0;">
                <li>Fotocopia de tu DNI (ambas caras)</li>
                <li>Fotocopia de tu licencia de armas largas rayadas (tipo D)</li>
            </ul>
        </div>
    @elseif($documentType === 'escopeta')
        <div class="alert alert-warning">
            <p><strong>RECUERDA:</strong> Para poder enviar tu arma, necesitamos que nos envíes la siguiente documentación:</p>
            <ul style="padding-left: 20px; margin: 8px 0;">
                <li>Fotocopia de tu DNI (ambas caras)</li>
                <li>Fotocopia de una licencia de escopeta (tipo E)</li>
            </ul>
        </div>
    @elseif($documentType === 'dni')
        <div class="alert alert-warning">
            <p><strong>RECUERDA:</strong> Para poder enviar tu arma, necesitamos que nos envíes la siguiente documentación:</p>
            <ul style="padding-left: 20px; margin: 8px 0;">
                <li>Fotocopia de tu DNI (ambas caras)</li>
            </ul>
        </div>
    @else
        <div class="alert alert-warning">
            <p><strong>RECUERDA:</strong> Para poder enviar tu carabina de aire, debes proporcionarnos una copia de tu pasaporte o carnet de conducir (ambas caras si es una tarjeta).</p>
        </div>
    @endif

    <p style="margin-top: 20px;"><strong>Por favor, haz clic en el siguiente enlace y sigue las instrucciones:</strong></p>

    @if($uploadUrl)
        <div class="button-group">
            <a href="{{ $uploadUrl }}" class="button" target="_blank" rel="noopener">Subir documentación</a>
        </div>
    @else
        <p>Puedes subir los archivos necesarios usando el código de verificación anterior en nuestro portal de documentación, o respondiendo a este correo adjuntando los archivos.</p>
    @endif

    <div class="alert alert-info">
        <p><strong>⏰ Importante:</strong> Te enviaremos un recordatorio si no completas la carga de documentación en 1 día.</p>
    </div>

    <p><strong>¿Necesitas ayuda?</strong> Si tienes dudas sobre qué documentación enviar, responde a este correo y te asistiremos con gusto.</p>

    <p>Saludos,<br/>
    <strong>El equipo de Soporte</strong></p>
@endsection
