@extends('mailers.documents.layouts.document')

@section('title', 'Mensaje de Alsernet')
@section('header', 'Mensaje Personalizado')
@section('header-subtitle', 'Información importante para ti')

@section('content')
    <div class="greeting">
        Hola {{ $customerName }},
    </div>

    <!-- Personalizado Content -->
    <div class="message-content" style="white-space: pre-wrap; word-wrap: break-word;">
    {{ $content }}
    </div>

    <!-- Divider -->
    <div class="divider"></div>

    <!-- Closing Message -->
    <div style="margin-top: 20px;">
        <p>Si tienes alguna duda o necesitas ayuda, no dudes en contactarnos.</p>
    </div>
@endsection
