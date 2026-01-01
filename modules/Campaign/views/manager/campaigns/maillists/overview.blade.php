@extends('layouts.managers')

@section('content')

    @include('theme.components.card', ['title' => 'Estadistica lista '. $list->title])

@endsection

@push('scripts')

@endpush
