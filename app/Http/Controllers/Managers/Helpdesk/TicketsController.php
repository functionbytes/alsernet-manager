<?php

namespace App\Http\Controllers\Managers\Helpdesk;

use App\Events\Helpdesk\TicketCreated;
use App\Events\Helpdesk\TicketMessageReceived;
use App\Events\Helpdesk\TicketStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesks\StoreTicketRequest;
use App\Http\Requests\Helpdesks\UpdateTicketRequest;
use App\Models\Helpdesk\Customer;
use App\Models\Helpdesk\Group;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketCategory;
use App\Models\Helpdesk\TicketSlaPolicy;
use App\Models\Helpdesk\TicketStatus;
use App\Models\Helpdesk\TicketView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketsController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index(Request $request)
    {
        $this->authorize('manager.helpdesk.tickets.index');

        $userId = auth()->id();

        // Get all views available for the current user
        $views = TicketView::forUser($userId)
            ->ordered()
            ->get();

        // Get current view - use viewId from request or default view
        $currentView = null;
        if ($request->has('viewId')) {
            $currentView = $views->firstWhere('id', $request->viewId);
        }

        // If no view selected or view not found, use default or first view
        if (! $currentView) {
            $currentView = $views->firstWhere('is_default', true) ?? $views->first();
        }

        $query = Ticket::query()
            ->with(['customer', 'status', 'category', 'assignee'])
            ->latest();

        // Apply view filters if a view is selected
        if ($currentView && ! empty($currentView->filters)) {
            $this->applyViewFilters($query, $currentView->filters);
        }

        // Apply additional URL filters (these override view filters)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status_id', $request->status);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('assignee') && $request->assignee !== 'all') {
            if ($request->assignee === 'mine') {
                $query->where('assignee_id', $userId);
            } elseif ($request->assignee === 'unassigned') {
                $query->whereNull('assignee_id');
            } else {
                $query->where('assignee_id', $request->assignee);
            }
        }

        // Filter by group
        if ($request->has('group') && $request->group !== 'all') {
            $group = Group::find($request->group);
            if ($group) {
                $userIds = $group->users()->pluck('users.id');
                $query->whereIn('assignee_id', $userIds);
            }
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        // SLA filters
        if ($request->has('sla_status')) {
            if ($request->sla_status === 'breach') {
                $query->slaBreach();
            } elseif ($request->sla_status === 'warning') {
                $query->slaWarning();
            }
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->search($search);
        }

        if ($request->has('archived')) {
            $query->where('is_archived', (bool) $request->archived);
        } else {
            $query->where('is_archived', false);
        }

        $tickets = $query->paginate(50)->appends($request->query());
        $statuses = TicketStatus::active()->ordered()->get();
        $categories = TicketCategory::active()->ordered()->get();
        $groups = Group::orderBy('name')->get();

        return view('managers.views.helpdesk.tickets.index', [
            'tickets' => $tickets,
            'statuses' => $statuses,
            'categories' => $categories,
            'groups' => $groups,
            'views' => $views,
            'currentView' => $currentView,
            'filters' => $request->only(['status', 'category', 'assignee', 'group', 'priority', 'source', 'sla_status', 'search', 'archived']),
        ]);
    }

    /**
     * Apply filters from a saved view
     */
    protected function applyViewFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all') {
                continue;
            }

            match ($key) {
                'status_id' => $query->where('status_id', $value),
                'category_id' => $query->where('category_id', $value),
                'assignee' => $value === 'mine'
                    ? $query->where('assignee_id', auth()->id())
                    : ($value === 'unassigned'
                        ? $query->whereNull('assignee_id')
                        : $query->where('assignee_id', $value)),
                'group' => (function () use ($query, $value) {
                    $group = Group::find($value);
                    if ($group) {
                        $userIds = $group->users()->pluck('users.id');
                        $query->whereIn('assignee_id', $userIds);
                    }
                })(),
                'priority' => $query->where('priority', $value),
                'source' => $query->where('source', $value),
                'is_open' => $query->whereHas('status', fn ($q) => $q->where('is_open', (bool) $value)),
                'is_archived' => $query->where('is_archived', (bool) $value),
                'sla_breach' => (bool) $value ? $query->slaBreach() : null,
                default => null,
            };
        }
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(Request $request)
    {
        $this->authorize('manager.helpdesk.tickets.create');

        $customer = null;
        if ($request->has('customer')) {
            $customer = Customer::findOrFail($request->customer);
        }

        $customers = Customer::orderBy('name')->get();
        $categories = TicketCategory::active()->ordered()->get();
        $statuses = TicketStatus::active()->ordered()->get();
        $defaultStatus = TicketStatus::where('is_default', true)->first() ?? $statuses->first();
        $slaPolicies = TicketSlaPolicy::active()->get();
        $groups = Group::orderBy('name')->get();

        // Get available agents (users with helpdesk permissions)
        $agents = \App\Models\User::where('available', 1)
            ->where('confirmed', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('managers.views.helpdesk.tickets.create', [
            'customer' => $customer,
            'customers' => $customers,
            'categories' => $categories,
            'statuses' => $statuses,
            'defaultStatus' => $defaultStatus,
            'slaPolicies' => $slaPolicies,
            'groups' => $groups,
            'agents' => $agents,
        ]);
    }

    /**
     * Store a newly created ticket
     */
    public function store(StoreTicketRequest $request)
    {
        DB::transaction(function () use ($request, &$ticket) {
            // Create the ticket
            $data = $request->validated();

            // Set SLA policy from category if not provided
            if (! isset($data['sla_policy_id']) && isset($data['category_id'])) {
                $category = TicketCategory::find($data['category_id']);
                if ($category && $category->default_sla_policy_id) {
                    $data['sla_policy_id'] = $category->default_sla_policy_id;
                }
            }

            $ticket = Ticket::create($data);

            // Handle attachments if present
            if ($request->hasFile('attachments')) {
                $attachmentUrls = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('helpdesk/tickets/'.$ticket->id, 'public');
                    $attachmentUrls[] = Storage::url($path);
                }

                // Create first item with attachments if description exists
                if (! empty($data['description'])) {
                    $ticket->items()->create([
                        'type' => 'message',
                        'user_id' => auth()->id(),
                        'body' => $data['description'],
                        'attachment_urls' => $attachmentUrls,
                        'is_internal' => false,
                    ]);
                }
            } elseif (! empty($data['description'])) {
                // Create first item with description
                $ticket->items()->create([
                    'type' => 'message',
                    'user_id' => auth()->id(),
                    'body' => $data['description'],
                    'is_internal' => false,
                ]);
            }

            // Broadcast event
            broadcast(new TicketCreated($ticket));
        });

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket creado exitosamente. Número: '.$ticket->ticket_number);
    }

    /**
     * Display the specified ticket
     */
    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.show');

        // Load relationships
        $ticket->load(['customer', 'status', 'category', 'assignee', 'group', 'slaPolicy', 'items.user', 'items.author', 'watchers']);

        // Get sidebar ticket list (same filters as index)
        $sidebarQuery = Ticket::query()
            ->with(['customer', 'status', 'category'])
            ->latest();

        // Apply same filters as index for consistency
        if ($request->has('status') && $request->status !== 'all') {
            $sidebarQuery->where('status_id', $request->status);
        }

        $tickets = $sidebarQuery->paginate(20);

        // Get available statuses and categories for quick actions
        $statuses = TicketStatus::active()->ordered()->get();
        $categories = TicketCategory::active()->ordered()->get();
        $groups = Group::orderBy('name')->get();

        // Mark items as read for current user
        foreach ($ticket->items as $item) {
            $item->markAsRead(auth()->id());
        }

        return view('managers.views.helpdesk.tickets.show', [
            'ticket' => $ticket,
            'tickets' => $tickets,
            'statuses' => $statuses,
            'categories' => $categories,
            'groups' => $groups,
        ]);
    }

    /**
     * Show the form for editing the ticket
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.update');

        $categories = TicketCategory::active()->ordered()->get();
        $statuses = TicketStatus::active()->ordered()->get();
        $slaPolices = TicketSlaPolicy::active()->get();
        $groups = Group::orderBy('name')->get();
        $agents = \App\Models\User::where('available', 1)
            ->where('confirmed', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('managers.views.helpdesk.tickets.edit', [
            'ticket' => $ticket,
            'categories' => $categories,
            'statuses' => $statuses,
            'slaPolicies' => $slaPolices,
            'groups' => $groups,
            'agents' => $agents,
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->getModifiableFields();

        // Track what changed for system events
        $changes = [];

        if (isset($data['status_id']) && $data['status_id'] != $ticket->status_id) {
            $oldStatus = $ticket->status;
            $ticket->update(['status_id' => $data['status_id']]);
            $newStatus = $ticket->fresh()->status;

            // Create status change event
            $ticket->items()->create([
                'type' => 'status_change',
                'user_id' => auth()->id(),
                'body' => "Estado cambiado de '{$oldStatus->name}' a '{$newStatus->name}'",
                'metadata' => [
                    'old_status_id' => $oldStatus->id,
                    'new_status_id' => $newStatus->id,
                ],
            ]);

            // Handle SLA pause/resume
            if ($newStatus->stops_sla_timer && ! $oldStatus->stops_sla_timer) {
                $ticket->pauseSla();
            } elseif (! $newStatus->stops_sla_timer && $oldStatus->stops_sla_timer) {
                $ticket->resumeSla();
            }

            broadcast(new TicketStatusChanged($ticket));
            unset($data['status_id']);
        }

        if (isset($data['priority']) && $data['priority'] != $ticket->priority) {
            $changes['priority'] = ['old' => $ticket->priority, 'new' => $data['priority']];

            $ticket->items()->create([
                'type' => 'priority_changed',
                'user_id' => auth()->id(),
                'body' => "Prioridad cambiada de '{$ticket->priority}' a '{$data['priority']}'",
                'metadata' => $changes['priority'],
            ]);
        }

        if (isset($data['category_id']) && $data['category_id'] != $ticket->category_id) {
            $oldCategory = $ticket->category;
            $changes['category_id'] = ['old' => $ticket->category_id, 'new' => $data['category_id']];

            $ticket->update(['category_id' => $data['category_id']]);
            $newCategory = $ticket->fresh()->category;

            $ticket->items()->create([
                'type' => 'category_changed',
                'user_id' => auth()->id(),
                'body' => "Categoría cambiada de '{$oldCategory->name}' a '{$newCategory->name}'",
                'metadata' => $changes['category_id'],
            ]);

            unset($data['category_id']);
        }

        if (isset($data['assignee_id']) && $data['assignee_id'] != $ticket->assignee_id) {
            if ($data['assignee_id']) {
                $ticket->assignTo($data['assignee_id']);
            } else {
                $ticket->update(['assignee_id' => null, 'assigned_at' => null]);
                $ticket->items()->create([
                    'type' => 'unassigned',
                    'user_id' => auth()->id(),
                    'body' => 'Ticket desasignado',
                ]);
            }
            unset($data['assignee_id']);
        }

        // Update remaining fields
        if (! empty($data)) {
            $ticket->update($data);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket actualizado exitosamente.');
    }

    /**
     * Remove the specified ticket (soft delete)
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.delete');

        $ticket->delete();

        return redirect()
            ->route('manager.helpdesk.tickets.index')
            ->with('success', 'Ticket eliminado exitosamente.');
    }

    /**
     * Close the ticket
     */
    public function close(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.close');

        $ticket->close();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket cerrado exitosamente.',
                'ticket' => $ticket->fresh(),
            ]);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket cerrado exitosamente.');
    }

    /**
     * Mark ticket as resolved
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.resolve');

        $ticket->resolve();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket marcado como resuelto.',
                'ticket' => $ticket->fresh(),
            ]);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket marcado como resuelto.');
    }

    /**
     * Reopen a closed ticket
     */
    public function reopen(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.reopen');

        $ticket->reopen();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket reabierto exitosamente.',
                'ticket' => $ticket->fresh(),
            ]);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket reabierto exitosamente.');
    }

    /**
     * Archive the ticket
     */
    public function archive(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.archive');

        $ticket->archive();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket archivado exitosamente.',
            ]);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.index')
            ->with('success', 'Ticket archivado exitosamente.');
    }

    /**
     * Add a message to the ticket
     */
    public function storeMessage(Request $request, Ticket $ticket)
    {
        $this->authorize('manager.helpdesk.tickets.reply');

        $request->validate([
            'body' => 'required|string|min:1|max:10000',
            'is_internal' => 'sometimes|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar',
        ]);

        $attachmentUrls = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('helpdesk/tickets/'.$ticket->id, 'public');
                $attachmentUrls[] = Storage::url($path);
            }
        }

        $item = $ticket->items()->create([
            'type' => 'message',
            'user_id' => auth()->id(),
            'body' => $request->body,
            'attachment_urls' => $attachmentUrls,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Update ticket's last_message_at
        $ticket->update(['last_message_at' => now()]);

        // Set first_response_at if this is the first agent response
        if (! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        // Broadcast event
        broadcast(new TicketMessageReceived($ticket, $item));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente.',
                'item' => $item->load(['user']),
            ]);
        }

        return redirect()
            ->route('manager.helpdesk.tickets.show', $ticket)
            ->with('success', 'Mensaje enviado exitosamente.');
    }
}
