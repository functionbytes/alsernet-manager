@extends('layouts.theme')

@section('content')
    @include('core::components.card', ['title' => 'Calendario de eventos'])

    @include('core::components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold">Gestión de eventos</h5>
                    <p class="text-muted mb-0 small">Visualiza y administra todos tus eventos en un calendario interactivo</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('manager.events.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear evento
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div id="calendar" class="fc-calendar-container"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('theme/libs/fullcalendar/index.global.min.js') }}"></script>
<script src="{{ asset('theme/libs/moment/min/moment.min.js') }}"></script>

<style>
    .fc-calendar-container {
        padding: 1.5rem;
        background: #fff;
    }

    /* Estilos del calendario */
    .fc {
        font-family: inherit;
    }

    .fc .fc-button-primary {
        background-color: #90bb13;
        border-color: #90bb13;
        color: white;
        font-size: 0.875rem;
    }

    .fc .fc-button-primary:hover {
        background-color: #7aa80f;
        border-color: #7aa80f;
    }

    .fc .fc-button-primary.fc-button-active {
        background-color: #7aa80f;
        border-color: #7aa80f;
    }

    .fc .fc-col-header-cell {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        font-weight: 600;
        color: #495057;
        padding: 0.875rem 0.5rem;
    }

    .fc .fc-daygrid-day {
        border-color: #e9ecef;
    }

    .fc .fc-daygrid-day:hover {
        background-color: #f8f9fa;
    }

    .fc .fc-daygrid-day.fc-day-other {
        background-color: #fafbfc;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: #e7f5cc;
    }

    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        color: #90bb13;
        font-weight: 700;
    }

    .fc .fc-daygrid-day-number {
        padding: 0.5rem;
        color: #495057;
    }

    .fc .fc-event {
        border: none;
        border-radius: 0.375rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        font-size: 0.8125rem;
    }

    .fc .fc-event-title {
        font-weight: 600;
        padding: 0.25rem 0;
    }

    .fc-event-success {
        background-color: #13C672;
    }

    .fc-event-primary {
        background-color: #90bb13;
    }

    .fc-event-danger {
        background-color: #FA896B;
    }

    .fc-event-warning {
        background-color: #FEC90F;
        color: #333;
    }

    .fc-event-info {
        background-color: #2196F3;
    }

    .fc .fc-list-event-title {
        font-weight: 600;
        color: #495057;
    }

    .fc .fc-list-event {
        border-color: #e9ecef;
    }

    .fc .fc-list-event:hover {
        background-color: #f8f9fa;
    }

    .fc .fc-timegrid-slot {
        height: 3em;
        border-color: #e9ecef;
    }

    .fc .fc-timegrid-slot-label {
        background-color: #f8f9fa;
        color: #6c757d;
        font-size: 0.8rem;
    }

    /* Mejoras responsivas */
    @media (max-width: 576px) {
        .fc-calendar-container {
            padding: 1rem;
        }

        .fc .fc-button-group {
            flex-wrap: wrap;
        }

        .fc .fc-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>

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
        eventDisplay: 'block',
        events: {
            url: '{{ route("manager.events.calendar.events") }}',
            failure: function() {
                console.error('Error al cargar los eventos');
            }
        },
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        eventDidMount: function(info) {
            // Agregar clase de color según tipo de evento si existe
            if (info.event.extendedProps.color) {
                info.el.classList.add('fc-event-' + info.event.extendedProps.color);
            } else {
                info.el.classList.add('fc-event-primary');
            }

            // Mejorar el cursor al pasar por eventos
            info.el.style.cursor = 'pointer';
        },
        editable: false,
        selectable: true,
        height: 'auto',
        contentHeight: 'auto',
        datesSet: function(info) {
            // Recargar eventos cuando cambia el rango de fechas
        }
    });
    calendar.render();
});
</script>
@endpush
