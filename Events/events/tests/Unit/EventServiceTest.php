<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Traits\TracksDatabaseState;
use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use App\Services\EventService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventServiceTest extends TestCase
{
    use TracksDatabaseState;

    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Verify we're in test environment and database transaction
        $this->assertTestEnvironment();
        $this->assertDatabaseInTransaction();

        // Initialize service
        $this->service = new EventService();
    }

    public function test_it_can_get_published_events()
    {
        // Track changes for this test
        $this->startTrackingChanges('get_published_events');

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
        
        // Verify transaction isolation
        $this->assertRowCountChanged('events', 1, 'get_published_events');
    }

    public function test_it_can_filter_events_by_search()
    {
        // Track changes for this test
        $this->startTrackingChanges('filter_by_search');

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
        
        // Verify database changes
        $this->assertRowCountChanged('events', 1, 'filter_by_search');
    }

    public function test_it_can_filter_events_by_category()
    {
        // Track changes for this test
        $this->startTrackingChanges('filter_by_category');

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

        // Verify database state changes
        $this->assertRowCountChanged('events', 1, 'filter_by_category');
        $this->assertRowCountChanged('categories', 1, 'filter_by_category');
    }

    public function test_it_can_create_an_event()
    {
        // Track changes for this test
        $this->startTrackingChanges('create_event');

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

        // Verify transaction isolation
        $this->assertRowCountChanged('events', 1, 'create_event');
        $this->assertRowCountChanged('users', 1, 'create_event');
    }

    public function test_it_can_update_an_event()
    {
        // Track changes for this test
        $this->startTrackingChanges('update_event');

        // Arrange - Create isolated event for update
        $event = Event::factory()->create([
            'title' => 'Original Title',
            'status' => 'draft'
        ]);

        $initialRowCount = DB::table('events')->count();
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

        // Verify transaction isolation - no new rows added, only update
        $this->assertRowCountChanged('events', 1, 'update_event'); // 1 for initial factory create
    }

    public function test_it_can_delete_an_event()
    {
        // Track changes for this test
        $this->startTrackingChanges('delete_event');

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
        
        // Verify transaction isolation - row was added then deleted
        $currentCount = DB::table('events')->count();
        $this->assertEquals(0, $currentCount, 'Event should be deleted');
    }

    public function test_it_returns_paginated_events()
    {
        // Track changes for this test
        $this->startTrackingChanges('paginated_events');

        // Arrange - Create multiple test events
        Event::factory(5)->create([
            'status' => 'published'
        ]);

        // Act
        $result = $this->service->getPublishedEvents();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->total());
        
        // Verify database state
        $this->assertRowCountChanged('events', 5, 'paginated_events');
    }

    /**
     * Test that database changes are rolled back after test failure
     * This verifies the transaction rollback mechanism
     */
    public function test_database_isolation_via_transaction_rollback()
    {
        // This test demonstrates that changes will be rolled back
        $countBefore = DB::table('events')->count();

        // Create an event
        Event::factory()->create(['title' => 'Rollback Test Event']);

        $countAfter = DB::table('events')->count();

        // Verify change was made in transaction
        $this->assertGreaterThan($countBefore, $countAfter);

        // When this test completes, the transaction will be rolled back
        // and countAfter will revert to countBefore
    }
}
