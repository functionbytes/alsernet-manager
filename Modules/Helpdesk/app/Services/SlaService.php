<?php

namespace Modules\Helpdesk\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Helpdesk\Models\Ticket;
use Modules\Helpdesk\Models\SlaPolicy;
use Modules\Helpdesk\Events\SlaBreached;
use Modules\Helpdesk\Events\SlaWarning;

class SlaService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Calculate due date for a ticket based on SLA policy
     */
    public function calculateDueDate(Ticket $ticket): ?Carbon
    {
        try {
            $policy = $this->getApplicablePolicy($ticket);

            if (!$policy) {
                return null;
            }

            $hours = $policy->resolution_time_hours ?? 24;
            $dueDate = now();

            if ($policy->business_hours_only) {
                $dueDate = $this->addBusinessHours($dueDate, $hours);
            } else {
                $dueDate = $dueDate->addHours($hours);
            }

            return $dueDate;
        } catch (\Exception $e) {
            Log::error('Error calculating due date', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check for SLA breaches and mark them
     */
    public function checkBreaches(): Collection
    {
        try {
            $breachedTickets = Ticket::where('sla_breached', false)
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->whereNull('closed_at')
                ->get();

            foreach ($breachedTickets as $ticket) {
                $ticket->update(['sla_breached' => true]);

                event(new SlaBreached($ticket));

                $this->notificationService->notifySlaBreach($ticket);

                Log::warning('SLA breach detected', [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'due_at' => $ticket->due_at
                ]);
            }

            return $breachedTickets;
        } catch (\Exception $e) {
            Log::error('Error checking SLA breaches', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Send warnings for tickets approaching SLA breach
     */
    public function sendWarnings(): int
    {
        try {
            $warningTickets = Ticket::where('sla_breached', false)
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addMinutes(30)])
                ->whereNull('closed_at')
                ->get();

            $sentCount = 0;

            foreach ($warningTickets as $ticket) {
                $policy = $this->getApplicablePolicy($ticket);

                if (!$policy) {
                    continue;
                }

                $totalMinutes = $ticket->created_at->diffInMinutes($ticket->due_at);
                $usedMinutes = $ticket->created_at->diffInMinutes(now());
                $percentUsed = ($usedMinutes / $totalMinutes) * 100;

                $warningThreshold = $policy->warning_threshold_percent ?? 80;

                if ($percentUsed >= $warningThreshold) {
                    event(new SlaWarning($ticket));

                    $this->notificationService->notifySlaWarning($ticket);

                    $sentCount++;

                    Log::info('SLA warning sent', [
                        'ticket_id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'percent_used' => round($percentUsed, 2)
                    ]);
                }
            }

            return $sentCount;
        } catch (\Exception $e) {
            Log::error('Error sending SLA warnings', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get tickets that have breached SLA
     */
    public function getBreachedTickets(?int $agentId = null): Collection
    {
        $query = Ticket::where('sla_breached', true)
            ->whereNull('closed_at');

        if ($agentId) {
            $query->where('assigned_to', $agentId);
        }

        return $query->orderBy('due_at', 'asc')->get();
    }

    /**
     * Get tickets approaching SLA breach
     */
    public function getUpcomingBreaches(int $hoursAhead = 24): Collection
    {
        return Ticket::where('sla_breached', false)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [now(), now()->addHours($hoursAhead)])
            ->whereNull('closed_at')
            ->orderBy('due_at', 'asc')
            ->get();
    }

    /**
     * Calculate response time in minutes
     */
    public function calculateResponseTime(Ticket $ticket): ?int
    {
        if (!$ticket->first_response_at) {
            return null;
        }

        return $ticket->created_at->diffInMinutes($ticket->first_response_at);
    }

    /**
     * Calculate resolution time in minutes
     */
    public function calculateResolutionTime(Ticket $ticket): ?int
    {
        if (!$ticket->closed_at) {
            return null;
        }

        return $ticket->created_at->diffInMinutes($ticket->closed_at);
    }

    /**
     * Get applicable SLA policy for a ticket
     */
    public function getApplicablePolicy(Ticket $ticket): ?SlaPolicy
    {
        $policy = SlaPolicy::where('priority_id', $ticket->priority_id)
            ->where('category_id', $ticket->category_id)
            ->where('is_active', true)
            ->first();

        if (!$policy) {
            $policy = SlaPolicy::where('priority_id', $ticket->priority_id)
                ->whereNull('category_id')
                ->where('is_active', true)
                ->first();
        }

        return $policy;
    }

    /**
     * Add business hours to a datetime
     */
    private function addBusinessHours(Carbon $startDate, int $hours): Carbon
    {
        $date = $startDate->copy();
        $remainingHours = $hours;

        while ($remainingHours > 0) {
            $date->addHour();

            if ($date->isWeekday() && $date->hour >= 9 && $date->hour < 18) {
                $remainingHours--;
            }
        }

        return $date;
    }
}
