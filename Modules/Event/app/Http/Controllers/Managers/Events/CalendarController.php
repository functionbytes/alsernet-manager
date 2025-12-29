<?php

namespace Modules\Event\Http\Controllers\Managers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Event\Models\Event;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('event::managers.events.calendar');
    }

    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $events = Event::whereBetween('start_at', [$start, $end])
            ->where('available', 1)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->uid,
                    'title' => $event->title,
                    'start' => $event->start_at,
                    'end' => $event->end_at,
                    'backgroundColor' => $event->color_flag ?? '#90bb13',
                    'url' => route('manager.events.view', $event->uid),
                ];
            });

        return response()->json($events);
    }
}
