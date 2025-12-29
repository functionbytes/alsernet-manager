<?php

namespace Modules\Event\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Event\Models\Event;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test viewing events index page
     */
    public function test_can_view_events_index(): void
    {
        $this->actingAs($this->createUser())
            ->get(route('manager.events'))
            ->assertStatus(200)
            ->assertViewIs('event::managers.events.index');
    }

    /**
     * Test viewing create event form
     */
    public function test_can_view_create_event_form(): void
    {
        $this->actingAs($this->createUser())
            ->get(route('manager.events.create'))
            ->assertStatus(200)
            ->assertViewIs('event::managers.events.create');
    }

    /**
     * Test creating an event
     */
    public function test_can_create_event(): void
    {
        $data = [
            'title' => 'Test Event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'available' => 1,
            'color_flag' => '#90bb13',
        ];

        $response = $this->actingAs($this->createUser())
            ->post(route('manager.events.store'), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'uid', 'message']);

        $this->assertDatabaseHas('aalv_Alsernet_event_manager', [
            'title' => 'TEST EVENT',
            'available' => 1,
        ], 'prestashop');
    }

    /**
     * Test viewing edit event form
     */
    public function test_can_view_edit_event_form(): void
    {
        $event = $this->createEvent();

        $this->actingAs($this->createUser())
            ->get(route('manager.events.edit', $event->uid))
            ->assertStatus(200)
            ->assertViewIs('event::managers.events.edit')
            ->assertViewHas('event', $event);
    }

    /**
     * Test updating an event
     */
    public function test_can_update_event(): void
    {
        $event = $this->createEvent();

        $data = [
            'uid' => $event->uid,
            'title' => 'Updated Event Title',
            'start_at' => $event->start_at,
            'end_at' => $event->end_at,
            'available' => 0,
        ];

        $response = $this->actingAs($this->createUser())
            ->post(route('manager.events.update'), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'uid', 'message']);

        $this->assertDatabaseHas('aalv_Alsernet_event_manager', [
            'uid' => $event->uid,
            'title' => 'UPDATED EVENT TITLE',
            'available' => 0,
        ], 'prestashop');
    }

    /**
     * Test viewing event details
     */
    public function test_can_view_event_details(): void
    {
        $event = $this->createEvent();

        $this->actingAs($this->createUser())
            ->get(route('manager.events.view', $event->uid))
            ->assertStatus(200)
            ->assertViewIs('event::managers.events.show')
            ->assertViewHas('event', $event);
    }

    /**
     * Test viewing calendar
     */
    public function test_can_view_calendar(): void
    {
        $this->actingAs($this->createUser())
            ->get(route('manager.events.calendar'))
            ->assertStatus(200)
            ->assertViewIs('event::managers.events.calendar');
    }

    /**
     * Test fetching calendar events
     */
    public function test_can_fetch_calendar_events(): void
    {
        $event = $this->createEvent();

        $response = $this->actingAs($this->createUser())
            ->get(route('manager.events.calendar.events'), [
                'start' => $event->start_at->format('Y-m-d'),
                'end' => $event->end_at->format('Y-m-d'),
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'start', 'end', 'backgroundColor', 'url']
            ]);
    }

    /**
     * Test deleting an event
     */
    public function test_can_delete_event(): void
    {
        $event = $this->createEvent();

        $this->actingAs($this->createUser())
            ->get(route('manager.events.destroy', $event->uid))
            ->assertRedirect(route('manager.events'));

        $this->assertSoftDeleted('aalv_Alsernet_event_manager', [
            'uid' => $event->uid,
        ], 'prestashop');
    }

    /**
     * Test event requires title
     */
    public function test_event_requires_title(): void
    {
        $data = [
            'title' => '',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ];

        $response = $this->actingAs($this->createUser())
            ->post(route('manager.events.store'), $data);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test event end must be after start
     */
    public function test_event_end_must_be_after_start(): void
    {
        $data = [
            'title' => 'Test Event',
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDay(),
            'available' => 1,
        ];

        $response = $this->actingAs($this->createUser())
            ->post(route('manager.events.store'), $data);

        $response->assertSessionHasErrors('end_at');
    }

    /**
     * Test API listing events
     */
    public function test_api_can_list_events(): void
    {
        $this->createEvent();
        $this->createEvent();

        $response = $this->actingAs($this->createUser(), 'sanctum')
            ->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'uid', 'title', 'start_at', 'end_at']
                ],
                'meta',
                'links'
            ]);
    }

    /**
     * Test API creating an event
     */
    public function test_api_can_create_event(): void
    {
        $data = [
            'title' => 'API Test Event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'available' => 1,
        ];

        $response = $this->actingAs($this->createUser(), 'sanctum')
            ->postJson('/api/events', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'uid', 'title']]);

        $this->assertDatabaseHas('aalv_Alsernet_event_manager', [
            'title' => 'API TEST EVENT',
        ], 'prestashop');
    }

    /**
     * Helper: Create test user
     */
    private function createUser()
    {
        return \App\Models\User::factory()->create();
    }

    /**
     * Helper: Create test event
     */
    private function createEvent(): Event
    {
        return Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'TEST EVENT',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'available' => 1,
        ]);
    }
}
