<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Collection;

class CategoryServiceTest extends TestCase
{
    protected CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Verify we're in test environment
        $this->assertTestEnvironment();
        
        $this->service = new CategoryService();
    }

    public function test_it_can_get_all_categories()
    {
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
    }

    public function test_it_can_get_multiple_categories()
    {
        // Arrange - Create multiple isolated test categories
        $categories = Category::factory(3)->create();

        // Act
        $result = $this->service->getAllCategories();

        // Assert
        $this->assertGreaterThanOrEqual(3, $result->count());
        
        foreach ($categories as $category) {
            $this->assertTrue($result->contains('id', $category->id));
        }
    }

    public function test_it_returns_all_categories_in_collection()
    {
        // Arrange
        Category::factory(5)->create();

        // Act
        $result = $this->service->getAllCategories();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }
}
