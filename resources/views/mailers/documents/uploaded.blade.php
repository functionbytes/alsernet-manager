@extends('mailers.documents.layouts.document')

@section('title', 'Confirmación de documentos')
@section('header', 'Documentación recibida')
@section('header-subtitle', 'Confirmación de envío')

@section('content')
    <div class="greeting">
        Hola {{ $customerName ?? 'cliente' }},
    </div>

    <p>Hemos recibido correctamente la documentación solicitada del pedido @if($orderReference){{ $orderReference }}@endif. Nuestro equipo revisará el material a la mayor brevedad y te informaremos cuando el proceso avance.</p>

    <div class="alert alert-success">
        <p><strong>✓ Documentación recibida exitosamente</strong></p>
    </div>

    <p>Si necesitas modificar o reenviar algún archivo, simplemente responde a este correo o contacta con tu gestor habitual.</p>

    <p>Gracias por confiar en nosotros.</p>
@endsection
