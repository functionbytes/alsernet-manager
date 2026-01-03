<?php

namespace Modules\Event\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Event\Http\Requests\Events\StoreEventRequest;
use Modules\Event\Http\Requests\Events\UpdateEventRequest;
use Modules\Event\Http\Resources\EventCollection;
use Modules\Event\Http\Resources\EventResource;
use Modules\Event\Models\Event;

class EventApiController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::descending()
            ->when($request->available !== null, fn ($q) => $q->where('available', $request->available))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->paginate($request->per_page ?? 15);

        return new EventCollection($events);
    }

    public function store(StoreEventRequest $request)
    {
        $event = new Event;
        $event->uid = Str::uuid()->toString();
        $event->fill($request->validated());
        $event->save();

        return new EventResource($event);
    }

    public function show(string $uid)
    {
        $event = Event::uid($uid);

        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, string $uid)
    {
        $event = Event::uid($uid);
        $event->fill($request->validated());
        $event->save();

        return new EventResource($event);
    }

    public function destroy(string $uid)
    {
        $event = Event::uid($uid);
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
