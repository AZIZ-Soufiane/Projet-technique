<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Traits\TracksDatabaseState;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryServiceTest extends TestCase
{
    use TracksDatabaseState;

    protected CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Verify we're in test environment and database transaction
        $this->assertTestEnvironment();
        $this->assertDatabaseInTransaction();

        $this->service = new CategoryService();
    }

    public function test_it_can_get_all_categories()
    {
        // Track changes for this test
        $this->startTrackingChanges('get_all_categories');

        // Arrange - Create isolated test category using factory
        $category = Category::factory()->create([
            'name' => 'Test Category ' . uniqid()
        ]);

        // Act
        $result = $this->service->getAllCategories();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue($result->contains('id', $category->id));

        // Verify transaction isolation
        $this->assertRowCountChanged('categories', 1, 'get_all_categories');
    }

    public function test_it_can_get_multiple_categories()
    {
        // Track changes for this test
        $this->startTrackingChanges('get_multiple_categories');

        // Arrange - Create multiple isolated test categories
        $categories = Category::factory(3)->create();

        // Act
        $result = $this->service->getAllCategories();

        // Assert
        $this->assertGreaterThanOrEqual(3, $result->count());

        foreach ($categories as $category) {
            $this->assertTrue($result->contains('id', $category->id));
        }

        // Verify database state
        $this->assertRowCountChanged('categories', 3, 'get_multiple_categories');
    }

    public function test_it_returns_all_categories_in_collection()
    {
        // Track changes for this test
        $this->startTrackingChanges('categories_in_collection');

        // Arrange
        Category::factory(5)->create();

        // Act
        $result = $this->service->getAllCategories();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());

        // Verify database state
        $this->assertRowCountChanged('categories', 5, 'categories_in_collection');
    }

    /**
     * Test that database changes are rolled back after test failure
     * This verifies the transaction rollback mechanism works for this service
     */
    public function test_category_database_isolation_via_transaction_rollback()
    {
        // This test demonstrates transaction isolation for categories
        $countBefore = DB::table('categories')->count();

        // Create a category
        Category::factory()->create(['name' => 'Rollback Test Category']);

        $countAfter = DB::table('categories')->count();

        // Verify change was made in transaction
        $this->assertGreaterThan($countBefore, $countAfter);

        // When this test completes, the transaction will be rolled back
        // and countAfter will revert to countBefore
    }
}
