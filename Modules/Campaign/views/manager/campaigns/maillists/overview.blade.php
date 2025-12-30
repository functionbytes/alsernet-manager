@extends('layouts.managers')

@section('content')

    @include('managers.components.card', ['title' => 'Estadistica lista '. $list->title])

@endsection

@push('scripts')

@endpush
