@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold">Calendario de eventos</h5>
                <a href="{{ route('manager.events.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>Crear evento
                </a>
            </div>

            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('theme/libs/fullcalendar/index.global.min.js') }}"></script>
<script src="{{ asset('theme/libs/moment/min/moment.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        events: '{{ route("manager.events.calendar.events") }}',
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        editable: false,
        selectable: true,
        height: 'auto'
    });
    calendar.render();
});
</script>
@endpush
