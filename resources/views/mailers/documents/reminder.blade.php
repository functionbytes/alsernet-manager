@extends('mailers.documents.layouts.document')

@section('title', 'Recordatorio de documentación')
@section('header', 'Necesitamos tu documentación')
@section('header-subtitle', 'Recordatorio importante')

@section('content')
    <div class="greeting">
        Hola {{ $customerName ?? 'cliente' }},
    </div>

    <p>Hemos confirmado el pago de tu pedido @if($orderReference)#{{ $orderReference }}@endif y necesitamos que completes la subida de la documentación solicitada para continuar con la gestión.</p>

    @if($uploadUrl)
        <p><strong>Puedes subir los archivos haciendo clic en el siguiente botón:</strong></p>
        <div class="button-group">
            <a href="{{ $uploadUrl }}" class="button" target="_blank" rel="noopener">Subir documentación</a>
        </div>
    @else
        <p>Utiliza el código anterior en nuestro portal de documentación o responde a este correo adjuntando los archivos necesarios.</p>
    @endif

    <div class="alert alert-info">
        <p>Si ya enviaste la documentación, por favor ignora este mensaje.</p>
    </div>

    <p>Gracias por tu colaboración.</p>
@endsection
