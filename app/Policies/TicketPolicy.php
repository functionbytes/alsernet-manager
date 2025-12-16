<?php

namespace App\Policies;

use App\Models\Helpdesk\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.index');
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Can view all tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.show.all')) {
            return true;
        }

        // Can view assigned tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.show.assigned') && $ticket->assignee_id === $user->id) {
            return true;
        }

        // Can view group tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.show.group') && $ticket->group_id && $user->groups->contains($ticket->group_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.create');
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Can update all tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.update.all')) {
            return true;
        }

        // Can update own assigned tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.update.assigned') && $ticket->assignee_id === $user->id) {
            return true;
        }

        // Can update group tickets
        if ($user->hasPermissionTo('manager.helpdesk.tickets.update.group') && $ticket->group_id && $user->groups->contains($ticket->group_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Can only delete open, unresolved tickets
        if ($ticket->isResolved() || $ticket->isClosed()) {
            return false;
        }

        return $user->hasPermissionTo('manager.helpdesk.tickets.delete');
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        // Can only close open tickets
        if ($ticket->isClosed()) {
            return false;
        }

        return $user->hasPermissionTo('manager.helpdesk.tickets.close');
    }

    /**
     * Determine whether the user can resolve the ticket.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        // Can only resolve open tickets
        if ($ticket->isClosed() || $ticket->isResolved()) {
            return false;
        }

        return $user->hasPermissionTo('manager.helpdesk.tickets.resolve');
    }

    /**
     * Determine whether the user can reopen the ticket.
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        // Can only reopen closed tickets
        if (! $ticket->isClosed()) {
            return false;
        }

        return $user->hasPermissionTo('manager.helpdesk.tickets.reopen');
    }

    /**
     * Determine whether the user can archive the ticket.
     */
    public function archive(User $user, Ticket $ticket): bool
    {
        // Can only archive closed tickets
        if (! $ticket->isClosed()) {
            return false;
        }

        return $user->hasPermissionTo('manager.helpdesk.tickets.archive');
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.assign');
    }

    /**
     * Determine whether the user can change ticket priority.
     */
    public function changePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.priority.change');
    }

    /**
     * Determine whether the user can restore the ticket.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.restore');
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('manager.helpdesk.tickets.force-delete');
    }
}
