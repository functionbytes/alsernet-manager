<?php

namespace Modules\Event\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Event\Models\Event;

class EventsController extends Controller
{
    public function index(Request $request): View
    {
        $searchKey = $request->search;
        $available = $request->available;

        $events = Event::descending();

        if ($searchKey) {
            $events->when(! strpos($searchKey, '-'), function ($query) use ($searchKey) {
                $query->where('title', 'like', '%'.$searchKey.'%');
            });
        }

        if ($available !== null) {
            $events = $events->where('available', $available);
        }

        $events = $events->paginate(10);

        return view('event::events.index')->with([
            'events' => $events,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create(): View
    {
        $options = $this->getFormOptions();

        return view('event::events.create')->with($options);
    }

    public function edit(string $uid): View
    {
        $event = Event::uid($uid);
        $options = $this->getFormOptions();

        return view('event::events.edit')->with([
            'event' => $event,
            ...$options,
        ]);
    }

    public function view(string $uid): View
    {
        $event = Event::uid($uid);

        return view('event::events.show')->with([
            'event' => $event,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $event = new Event;
        $event->uid = Str::uuid()->toString();
        $event->title = Str::upper($request->title);
        $event->color_flag = $request->color_flag;
        $event->filter_flag = $request->filter_flag;
        $event->management_flag = $request->management_flag;
        $event->color_buttom = $request->color_buttom;
        $event->hover_buttom = $request->hover_buttom;
        $event->cms = $request->cms;
        $event->featured = $request->featured;
        $event->amazing = $request->amazing;
        $event->available = $request->available;
        $event->completed = $request->completed;
        $event->iva = $request->iva;
        $event->processing = $request->processing;
        $event->processed = $request->processed;
        $event->banners = $request->banners;
        $event->banners_unique = $request->banners_unique;
        $event->banners_backup = $request->banners_backup;
        $event->start_at = $request->start_at;
        $event->end_at = $request->end_at;
        $event->save();

        return response()->json([
            'success' => true,
            'uid' => $event->uid,
            'message' => 'Evento creado correctamente',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $event = Event::uid($request->uid);
        $event->title = Str::upper($request->title);
        $event->color_flag = $request->color_flag;
        $event->filter_flag = $request->filter_flag;
        $event->management_flag = $request->management_flag;
        $event->color_buttom = $request->color_buttom;
        $event->hover_buttom = $request->hover_buttom;
        $event->cms = $request->cms;
        $event->featured = $request->featured;
        $event->amazing = $request->amazing;
        $event->available = $request->available;
        $event->completed = $request->completed;
        $event->iva = $request->iva;
        $event->processing = $request->processing;
        $event->processed = $request->processed;
        $event->banners = $request->banners;
        $event->banners_unique = $request->banners_unique;
        $event->banners_backup = $request->banners_backup;
        $event->start_at = $request->start_at;
        $event->end_at = $request->end_at;
        $event->save();

        return response()->json([
            'success' => true,
            'uid' => $event->uid,
            'message' => 'Evento actualizado correctamente',
        ]);
    }

    public function destroy(string $uid)
    {
        $event = Event::uid($uid);
        $event->delete();

        return redirect()->route('manager.events');
    }

    private function getFormOptions(): array
    {
        return [
            'availables' => collect([
                ['id' => '1', 'label' => 'Publico'],
                ['id' => '0', 'label' => 'Oculto'],
            ])->pluck('label', 'id'),
            'options' => collect([
                ['id' => '1', 'label' => 'Si'],
                ['id' => '0', 'label' => 'No'],
            ])->pluck('label', 'id'),
        ];
    }
}
