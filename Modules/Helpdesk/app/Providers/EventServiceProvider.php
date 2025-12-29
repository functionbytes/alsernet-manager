<?php

namespace Modules\Helpdesk\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Helpdesk\Events\{
    TicketCreated,
    TicketAssigned,
    TicketStatusChanged,
    MessageAdded,
    SlaBreached,
    TicketClosed
};
use Modules\Helpdesk\Listeners\{
    NotifyAgentOfAssignment,
    SendCustomerConfirmation,
    RecordTicketHistory,
    UpdateTicketLastActivity,
    RecalculateSla
};

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TicketCreated::class => [
            SendCustomerConfirmation::class,
            RecordTicketHistory::class,
        ],
        TicketAssigned::class => [
            NotifyAgentOfAssignment::class,
            RecordTicketHistory::class,
        ],
        TicketStatusChanged::class => [
            RecordTicketHistory::class,
            UpdateTicketLastActivity::class,
        ],
        MessageAdded::class => [
            UpdateTicketLastActivity::class,
        ],
        SlaBreached::class => [
            RecordTicketHistory::class,
        ],
        TicketClosed::class => [
            RecordTicketHistory::class,
        ],
    ];
}
