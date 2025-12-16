@extends('layouts.helpdesk')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - Helpdesk')

@section('content')
    {{-- Modern Helpdesk Header --}}
    <div class="bg-white border-bottom sticky-top" style="z-index: 10;">
        <div class="d-flex align-items-center justify-content-between px-4 py-3">
            <div class="d-flex align-items-center">
                <a href="{{ route('manager.helpdesk.tickets.index', request()->only(['viewId', 'group', 'category'])) }}" class="btn btn-light btn-sm me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-bold">
                    <span class="badge badge-sm" style="background-color: {{ $ticket->category->color }}">
                        <i class="{{ $ticket->category->icon }} me-1"></i>{{ $ticket->category->name }}
                    </span>
                    <span class="text-muted mx-2">#{{ $ticket->ticket_number }}</span>
                    {{ Str::limit($ticket->subject, 50) }}
                </h5>
            </div>
            <div class="btn-group">
                @can('update', $ticket)
                    <a href="{{ route('manager.helpdesk.tickets.edit', $ticket->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
                @can('close', $ticket)
                    @if(!$ticket->isClosed())
                        <form action="{{ route('manager.helpdesk.tickets.close', $ticket->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm" onclick="return confirm('¿Cerrar este ticket?')">
                                <i class="far fa-times-circle"></i> Cerrar
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="d-flex">
        {{-- Sidebar: Same as index --}}
        @include('managers.views.helpdesk.tickets.partials.sidebar')

        {{-- Tickets List (Slim Version) --}}
        <div class="w-20 flex-shrink-0 border-end">
            <div class="p-2 border-bottom bg-light">
                <small class="text-muted fw-semibold">
                    <i class="fas fa-ticket-alt fs-5 me-1"></i> {{ $tickets->total() }} tickets
                </small>
            </div>
            <ul class="list-group list-group-flush" style="height: calc(100vh - 140px); overflow-y: auto;" data-simplebar>
                @foreach($tickets as $t)
                    <li class="list-group-item list-group-item-action p-2 border-bottom {{ $t->id == $ticket->id ? 'active' : '' }}">
                        <a href="{{ route('manager.helpdesk.tickets.show', $t->id) }}" class="text-decoration-none d-block">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <small class="fw-semibold {{ $t->id == $ticket->id ? 'text-white' : 'text-dark' }}">
                                    #{{ $t->ticket_number }}
                                </small>
                                <small class="text-muted">{{ $t->created_at->diffForHumans(null, true) }}</small>
                            </div>
                            <small class="{{ $t->id == $ticket->id ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                                {{ Str::limit($t->subject, 30) }}
                            </small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Main Content: Ticket Detail --}}
        <div class="flex-fill border-end" style="width: 40%;">
            {{-- Ticket Header Info --}}
            <div class="p-3 border-bottom bg-light">
                <h5 class="mb-2">{{ $ticket->subject }}</h5>
                <div class="d-flex align-items-center gap-3 text-muted small">
                    <span><i class="fas fa-user"></i> {{ $ticket->customer->name }}</span>
                    <span><i class="far fa-envelope"></i> {{ $ticket->customer->email }}</span>
                    <span><i class="far fa-calendar"></i> {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Messages Thread --}}
            <div class="p-3" style="height: calc(100vh - 340px); overflow-y: auto;" data-simplebar id="messagesContainer">
                @forelse($ticket->items as $item)
                    @if($item->type == 'message' || $item->type == 'internal_note')
                        <div class="mb-3 {{ $item->isFromAgent() ? 'text-end' : '' }}">
                            <div class="d-inline-block text-start" style="max-width: 70%;">
                                {{-- Message Header --}}
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($item->isFromCustomer())
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ strtoupper(substr($item->sender_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong class="d-block">{{ $item->sender_name }}</strong>
                                        <small class="text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    @if($item->isFromAgent())
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ strtoupper(substr($item->sender_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Message Body --}}
                                <div class="card {{ $item->isFromAgent() ? 'bg-primary-subtle' : 'bg-light' }} {{ $item->type == 'internal_note' ? 'border-warning border-2' : '' }}">
                                    <div class="card-body p-3">
                                        @if($item->type == 'internal_note')
                                            <div class="badge bg-warning text-dark mb-2">
                                                <i class="fas fa-lock"></i> Nota Interna
                                            </div>
                                        @endif
                                        <div>{!! nl2br(e($item->body)) !!}</div>

                                        @if($item->attachment_urls)
                                            <div class="mt-2">
                                                @foreach($item->attachment_urls as $attachment)
                                                    <a href="{{ $attachment }}" target="_blank" class="btn btn-sm btn-light me-2 mb-1">
                                                        <i class="fas fa-paperclip"></i> {{ basename($attachment) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- System Event --}}
                        <div class="text-center my-3">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $item->event_label }}: {{ $item->body }}
                            </span>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-5">
                        <i class="far fa-comment-slash fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">No hay mensajes en este ticket</p>
                    </div>
                @endforelse
            </div>

            {{-- Reply Form --}}
            @can('update', $ticket)
                <div class="p-3 border-top bg-light">
                    <form action="{{ route('manager.helpdesk.tickets.messages.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <textarea name="body" class="form-control" rows="3" placeholder="Escribe tu respuesta..." required></textarea>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <input type="file" name="attachments[]" id="attachments" multiple class="d-none">
                                <label for="attachments" class="btn btn-light btn-sm">
                                    <i class="fas fa-paperclip"></i> Adjuntar
                                </label>
                                <div class="form-check form-check-inline ms-3">
                                    <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal" value="1">
                                    <label class="form-check-label" for="isInternal">
                                        <i class="fas fa-lock"></i> Nota interna
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="far fa-paper-plane"></i> Enviar
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>

        {{-- Right Sidebar: Ticket Info --}}
        <div class="flex-shrink-0" style="width: 20%;">
            {{-- SLA Status Panel --}}
            @if($ticket->slaPolicy)
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold mb-3">
                        <i class="far fa-clock"></i> Estado SLA
                    </h6>

                    {{-- First Response SLA --}}
                    @if($ticket->sla_first_response_due_at && !$ticket->first_response_at)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Primera Respuesta</small>
                            @php
                                $now = now();
                                $due = $ticket->sla_first_response_due_at;
                                $remaining = $now->diffInMinutes($due, false);
                                $total = $ticket->created_at->diffInMinutes($due);
                                $percentage = $total > 0 ? max(0, min(100, ($remaining / $total) * 100)) : 0;
                            @endphp

                            @if($ticket->sla_first_response_breached)
                                <div class="alert alert-danger p-2 mb-0">
                                    <i class="fas fa-exclamation-circle"></i> Incumplido
                                </div>
                            @elseif($percentage < 20)
                                <div class="alert alert-warning p-2 mb-1">
                                    <i class="far fa-clock"></i> Vence pronto
                                </div>
                                <small class="text-muted">{{ $due->diffForHumans() }}</small>
                            @else
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ $due->diffForHumans() }}</small>
                            @endif
                        </div>
                    @endif

                    {{-- Resolution SLA --}}
                    @if($ticket->sla_resolution_due_at && !$ticket->resolved_at)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Resolución</small>
                            @php
                                $now = now();
                                $due = $ticket->sla_resolution_due_at;
                                $remaining = $now->diffInMinutes($due, false);
                                $total = $ticket->created_at->diffInMinutes($due);
                                $percentage = $total > 0 ? max(0, min(100, ($remaining / $total) * 100)) : 0;
                            @endphp

                            @if($ticket->sla_resolution_breached)
                                <div class="alert alert-danger p-2 mb-0">
                                    <i class="fas fa-exclamation-circle"></i> Incumplido
                                </div>
                            @elseif($percentage < 20)
                                <div class="alert alert-warning p-2 mb-1">
                                    <i class="far fa-clock"></i> Vence pronto
                                </div>
                                <small class="text-muted">{{ $due->diffForHumans() }}</small>
                            @else
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ $due->diffForHumans() }}</small>
                            @endif
                        </div>
                    @endif

                    @if($ticket->sla_paused)
                        <div class="alert alert-info p-2 mb-0">
                            <i class="fas fa-pause"></i> SLA en pausa
                        </div>
                    @endif
                </div>
            @endif

            {{-- Ticket Details --}}
            <div class="p-3 border-bottom">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-info-circle"></i> Detalles
                </h6>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Estado</small>
                    <span class="badge" style="background-color: {{ $ticket->status->color }}">
                        {{ $ticket->status->name }}
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Prioridad</small>
                    @php
                        $priorityColors = ['urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', 'low' => 'secondary'];
                        $priorityColor = $priorityColors[$ticket->priority] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $priorityColor }}">{{ ucfirst($ticket->priority) }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Categoría</small>
                    <span class="badge" style="background-color: {{ $ticket->category->color }}">
                        <i class="{{ $ticket->category->icon }}"></i> {{ $ticket->category->name }}
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Asignado a</small>
                    @if($ticket->assignee)
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">
                                {{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}
                            </div>
                            <span>{{ $ticket->assignee->name }}</span>
                        </div>
                    @else
                        <span class="text-muted"><i class="fas fa-user-times"></i> Sin asignar</span>
                    @endif
                </div>

                @if($ticket->group)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Grupo</small>
                        <span><i class="fas fa-users-group"></i> {{ $ticket->group->name }}</span>
                    </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Origen</small>
                    @php
                        $sourceIcons = [
                            'manager' => 'ti-layout-dashboard',
                            'widget' => 'ti-message-circle',
                            'portal' => 'ti-world',
                            'api' => 'ti-api',
                            'email' => 'ti-mail'
                        ];
                        $sourceIcon = $sourceIcons[$ticket->source] ?? 'ti-help';
                    @endphp
                    <span><i class="ti {{ $sourceIcon }}"></i> {{ ucfirst($ticket->source) }}</span>
                </div>

                {{-- Custom Fields --}}
                @if($ticket->custom_fields)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2 fw-bold">Campos Personalizados</small>
                        @foreach($ticket->custom_fields as $key => $value)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                                <span class="small">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="p-3">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-bolt"></i> Acciones
                </h6>

                @can('resolve', $ticket)
                    @if(!$ticket->isResolved() && !$ticket->isClosed())
                        <form action="{{ route('manager.helpdesk.tickets.resolve', $ticket->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="far fa-check-circle"></i> Marcar como Resuelto
                            </button>
                        </form>
                    @endif
                @endcan

                @can('close', $ticket)
                    @if(!$ticket->isClosed())
                        <form action="{{ route('manager.helpdesk.tickets.close', $ticket->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm w-100" onclick="return confirm('¿Cerrar este ticket?')">
                                <i class="far fa-times-circle"></i> Cerrar Ticket
                            </button>
                        </form>
                    @endif
                @endcan

                @can('reopen', $ticket)
                    @if($ticket->isClosed())
                        <form action="{{ route('manager.helpdesk.tickets.reopen', $ticket->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-sync-alt"></i> Reabrir Ticket
                            </button>
                        </form>
                    @endif
                @endcan

                @can('archive', $ticket)
                    @if($ticket->isClosed() && !$ticket->is_archived)
                        <form action="{{ route('manager.helpdesk.tickets.archive', $ticket->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm w-100" onclick="return confirm('¿Archivar este ticket?')">
                                <i class="fas fa-archive"></i> Archivar
                            </button>
                        </form>
                    @endif
                @endcan

                @can('delete', $ticket)
                    <form action="{{ route('manager.helpdesk.tickets.destroy', $ticket->id) }}" method="POST" class="mb-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('¿Eliminar este ticket? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                @endcan
            </div>

            {{-- Activity Timeline --}}
            <div class="p-3 border-top">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-stream"></i> Actividad
                </h6>
                <div class="timeline-sm">
                    <div class="mb-2">
                        <small class="text-muted d-block">Creado</small>
                        <small>{{ $ticket->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    @if($ticket->assigned_at)
                        <div class="mb-2">
                            <small class="text-muted d-block">Asignado</small>
                            <small>{{ $ticket->assigned_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                    @if($ticket->first_response_at)
                        <div class="mb-2">
                            <small class="text-muted d-block">Primera Respuesta</small>
                            <small>{{ $ticket->first_response_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                    @if($ticket->resolved_at)
                        <div class="mb-2">
                            <small class="text-muted d-block">Resuelto</small>
                            <small>{{ $ticket->resolved_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                    @if($ticket->closed_at)
                        <div class="mb-2">
                            <small class="text-muted d-block">Cerrado</small>
                            <small>{{ $ticket->closed_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-scroll to bottom of messages
    const messagesContainer = document.getElementById('messagesContainer');
    if(messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Listen for new messages via Laravel Echo
    @if(config('broadcasting.default') !== 'null')
        Echo.channel('ticket.{{ $ticket->id }}')
            .listen('TicketMessageReceived', (e) => {
                location.reload();
            });
    @endif
</script>
@endpush
