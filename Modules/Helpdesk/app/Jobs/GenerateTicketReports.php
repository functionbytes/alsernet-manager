<?php

namespace Modules\Helpdesk\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Helpdesk\Models\Ticket;

/**
 * Job to generate daily ticket statistics reports
 */
class GenerateTicketReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("GenerateTicketReports job started at " . now());

            $yesterday = now()->subDay();
            $startOfDay = $yesterday->copy()->startOfDay();
            $endOfDay = $yesterday->copy()->endOfDay();

            $totalCreated = Ticket::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $totalClosed = Ticket::query()
                ->whereBetween('closed_at', [$startOfDay, $endOfDay])
                ->count();

            $averageResponseTime = Ticket::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('first_response_at')
                ->get()
                ->avg(function ($ticket) {
                    return $ticket->created_at->diffInMinutes($ticket->first_response_at);
                });

            $ticketsByCategory = Ticket::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->select('category_id', DB::raw('count(*) as total'))
                ->groupBy('category_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->category_id ?? 'uncategorized' => $item->total];
                })
                ->toArray();

            $ticketsByPriority = Ticket::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->priority => $item->total];
                })
                ->toArray();

            $agentPerformance = Ticket::query()
                ->whereBetween('closed_at', [$startOfDay, $endOfDay])
                ->whereNotNull('assigned_to')
                ->select('assigned_to', DB::raw('count(*) as tickets_closed'))
                ->groupBy('assigned_to')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->assigned_to => $item->tickets_closed];
                })
                ->toArray();

            $report = [
                'date' => $yesterday->toDateString(),
                'generated_at' => now(),
                'summary' => [
                    'total_created' => $totalCreated,
                    'total_closed' => $totalClosed,
                    'average_response_time_minutes' => round($averageResponseTime ?? 0, 2),
                ],
                'by_category' => $ticketsByCategory,
                'by_priority' => $ticketsByPriority,
                'agent_performance' => $agentPerformance,
            ];

            Log::info("Daily ticket report generated for {$yesterday->toDateString()}", $report);

            Log::info("GenerateTicketReports job completed at " . now(), [
                'report_date' => $yesterday->toDateString(),
                'total_created' => $totalCreated,
                'total_closed' => $totalClosed,
            ]);
        } catch (\Exception $e) {
            Log::error("GenerateTicketReports job failed: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
