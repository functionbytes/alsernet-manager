<?php

namespace Modules\Event\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Event\Models\Event;
use Modules\Event\Models\EventCategory;
use Modules\Event\Models\EventBanner;

class EventModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test event model creation
     */
    public function test_can_create_event(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
            'available' => 1,
        ]);

        $this->assertNotNull($event->id);
        $this->assertEquals('TEST EVENT', $event->title);
        $this->assertTrue($event->available);
    }

    /**
     * Test event scope descending
     */
    public function test_event_scope_descending(): void
    {
        $event1 = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Event 1',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $event2 = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Event 2',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $events = Event::descending()->get();

        $this->assertEquals($event2->id, $events->first()->id);
        $this->assertEquals($event1->id, $events->last()->id);
    }

    /**
     * Test event scope by uid
     */
    public function test_event_scope_by_uid(): void
    {
        $event = Event::create([
            'uid' => 'test-uid-123',
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $found = Event::uid('test-uid-123');

        $this->assertNotNull($found);
        $this->assertEquals($event->id, $found->id);
    }

    /**
     * Test event categories relationship
     */
    public function test_event_has_categories(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertIsIterable($event->categories);
    }

    /**
     * Test event banners relationship
     */
    public function test_event_has_banners(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertIsIterable($event->banners);
    }

    /**
     * Test event languages relationship
     */
    public function test_event_has_langs(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertIsIterable($event->langs);
    }

    /**
     * Test event attribute casting
     */
    public function test_event_casts_booleans(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
            'featured' => 1,
            'amazing' => true,
            'available' => 0,
        ]);

        $this->assertTrue($event->featured);
        $this->assertTrue($event->amazing);
        $this->assertFalse($event->available);
    }

    /**
     * Test event soft deletes
     */
    public function test_event_soft_deletes(): void
    {
        $event = Event::create([
            'uid' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => 'Test Event',
            'start_at' => now(),
            'end_at' => now()->addDay(),
        ]);

        $event->delete();

        $this->assertSoftDeleted($event);
        $this->assertNull(Event::uid($event->uid));
    }
}
