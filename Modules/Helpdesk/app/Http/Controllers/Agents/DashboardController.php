<?php

namespace Modules\Helpdesk\Http\Controllers\Agents;

use Illuminate\View\View;
use Modules\Helpdesk\Models\Status;
use Modules\Helpdesk\Models\Ticket;
use Modules\Helpdesk\Services\SlaService;

class DashboardController
{
    public function index(SlaService $slaService): View
    {
        $user = auth()->user();
        $userId = $user->id;

        // Estadísticas
        $totalTickets = Ticket::count();
        $myTickets = Ticket::where('assigned_to', $userId)->count();
        $unassignedTickets = Ticket::whereNull('assigned_to')->count();
        $openStatus = Status::where('slug', 'new')->first()?->id;
        $openTickets = Ticket::where('status_id', $openStatus)->count();
        $breachedTickets = $slaService->getBreachedTickets($userId)->count();
        $upcomingBreaches = $slaService->getUpcomingBreaches(24)->count();

        // Mis tickets más recientes
        $recentTickets = Ticket::where('assigned_to', $userId)
            ->latest('last_activity_at')
            ->limit(10)
            ->get();

        return view('helpdesk::agents.dashboard', [
            'totalTickets' => $totalTickets,
            'myTickets' => $myTickets,
            'unassignedTickets' => $unassignedTickets,
            'openTickets' => $openTickets,
            'breachedTickets' => $breachedTickets,
            'upcomingBreaches' => $upcomingBreaches,
            'recentTickets' => $recentTickets,
        ]);
    }
}
