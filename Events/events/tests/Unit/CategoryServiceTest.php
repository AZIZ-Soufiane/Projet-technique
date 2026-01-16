<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoryServiceTest extends TestCase
{
    use DatabaseTransactions; // Use DatabaseTransactions to roll back database changes after the test

    protected CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoryService();
    }

    public function test_it_can_get_all_categories()
    {
        // Ensure we have at least one category to test
        $categoryName = 'Test Category ' . uniqid();
        Category::create(['name' => $categoryName]);

        $result = $this->service->getAllCategories();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue($result->contains('name', $categoryName));
    }
}
