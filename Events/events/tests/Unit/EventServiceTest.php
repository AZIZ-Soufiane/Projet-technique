<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use App\Services\EventService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventServiceTest extends TestCase
{
    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Verify we're in test environment
        $this->assertTestEnvironment();
        
        // Initialize service
        $this->service = new EventService();
    }

    public function test_it_can_get_published_events()
    {
        // Arrange - Create test data in isolation
        Event::factory()->create([
            'status' => 'published',
            'title' => 'Published Event'
        ]);
        
        // Act
        $result = $this->service->getPublishedEvents();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->total());
    }

    public function test_it_can_filter_events_by_search()
    {
        // Arrange - Create isolated test event
        $event = Event::factory()->create([
            'title' => 'Unique Test Event Title XYZ123',
            'status' => 'published'
        ]);

        // Act
        $result = $this->service->getAdminEvents([
            'search' => 'XYZ123'
        ]);

        // Assert
        $this->assertGreaterThan(0, $result->total());
        $this->assertTrue($result->pluck('id')->contains($event->id));
    }

    public function test_it_can_filter_events_by_category()
    {
        // Arrange - Create isolated test data
        $category = Category::factory()->create([
            'name' => 'Test Category ' . uniqid()
        ]);
        
        $event = Event::factory()->create([
            'status' => 'published'
        ]);
        
        $event->categories()->attach($category->id);

        // Act
        $result = $this->service->getAdminEvents([
            'category' => $category->id
        ]);

        // Assert
        $this->assertGreaterThan(0, $result->total());
        $this->assertTrue(
            $result->pluck('id')->contains($event->id),
            'Event should be returned when filtered by its category'
        );
    }

    public function test_it_can_create_an_event()
    {
        // Arrange
        Storage::fake('public');
        
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $data = [
            'title' => 'New Test Event ' . uniqid(),
            'description' => 'Description for new event',
            'event_date' => now()->addDays(10)->toDateTimeString(),
            'status' => 'draft',
            'categories' => [] 
        ];
        
        $image = UploadedFile::fake()->create('event.jpg', 100);

        // Act
        $event = $this->service->createEvent($data, $image);

        // Assert
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => $data['title']
        ]);
        Storage::disk('public')->assertExists($event->image);
    }

    public function test_it_can_update_an_event()
    {
        // Arrange - Create isolated event for update
        $event = Event::factory()->create([
            'title' => 'Original Title',
            'status' => 'draft'
        ]);

        $newTitle = 'Updated Title ' . uniqid();

        // Act
        $this->service->updateEvent($event, [
            'title' => $newTitle
        ]);

        // Assert
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => $newTitle
        ]);
    }

    public function test_it_can_delete_an_event()
    {
        // Arrange - Create isolated event for deletion
        $event = Event::factory()->create([
            'title' => 'Event to Delete',
            'status' => 'draft'
        ]);
        
        $eventId = $event->id;

        // Act
        $this->service->deleteEvent($event);

        // Assert
        $this->assertDatabaseMissing('events', ['id' => $eventId]);
    }

    public function test_it_returns_paginated_events()
    {
        // Arrange - Create multiple test events
        Event::factory(5)->create([
            'status' => 'published'
        ]);

        // Act
        $result = $this->service->getPublishedEvents();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->total());
    }
}
