<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventServiceTest extends TestCase
{
    use DatabaseTransactions;
    
    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();
    
        $this->service = new EventService();
    }

    public function test_it_can_get_published_events()
    {
        $result = $this->service->getPublishedEvents();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        
        // Check if we have published events from the CSV data
        if (Event::where('status', 'published')->exists()) {
             $this->assertGreaterThan(0, $result->total());
        }
    }

    public function test_it_can_filter_events_by_search()
    {
        $existingEvent = Event::first();
        $this->assertNotNull($existingEvent, 'No events found in database to test with.');

        $result = $this->service->getAdminEvents([
            'search' => $existingEvent->title
        ]);

        $this->assertGreaterThan(0, $result->total());
        // Verify the first result matches the search term or contains it
        $this->assertStringContainsString($existingEvent->title, $result->first()->title);
    }

    public function test_it_can_filter_events_by_category()
    {
        $eventWithCategory = Event::has('categories')->with('categories')->first();
        $this->assertNotNull($eventWithCategory, 'No events with categories found in database.');
        
        $category = $eventWithCategory->categories->first();

        $result = $this->service->getAdminEvents([
            'category' => $category->id
        ]);

        $this->assertGreaterThan(0, $result->total());
        foreach ($result as $event) {
            $this->assertTrue($event->categories->contains('id', $category->id));
        }
    }

    public function test_it_can_create_an_event()
    {
        Storage::fake('public');
        
        $data = [
            'title' => 'New Test Event ' . uniqid(),
            'description' => 'Description for new event',
            'event_date' => now()->addDays(10)->toDateTimeString(),
            'status' => 'draft',
            'categories' => [] 
        ];
        
        // Ensure a user exists to authenticate
        $user = User::first();
        if (!$user) {
             $user = User::factory()->create();
        }
        $this->actingAs($user);

        $image = UploadedFile::fake()->create('event.jpg', 100);

        $event = $this->service->createEvent($data, $image);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => $data['title']
        ]);
        
        // Verify image was stored
        $this->assertNotNull($event->image);
        Storage::disk('public')->assertExists($event->image);
    }

    public function test_it_can_update_an_event()
    {
        $event = Event::first();
        $this->assertNotNull($event, 'No event found to update.');

        $newTitle = 'Updated Title ' . uniqid();

        $this->service->updateEvent($event, [
            'title' => $newTitle
        ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => $newTitle
        ]);
    }

    public function test_it_can_delete_an_event()
    {
        $event = Event::first();
        $this->assertNotNull($event, 'No event found to delete.');
        
        $id = $event->id;
        
        $this->service->deleteEvent($event);
        
        $this->assertDatabaseMissing('events', ['id' => $id]);
    }

    public function test_it_returns_paginated_events()
    {
        $result = $this->service->getPublishedEvents();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertNotNull($result->total());
    }
}
