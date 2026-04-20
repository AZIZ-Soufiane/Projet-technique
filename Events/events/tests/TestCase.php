<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure test database is clean and fresh
        $this->refreshTestDatabase();
        
        // Run migrations
        $this->artisan('migrate', [
            '--database' => 'sqlite',
            '--path' => 'database/migrations',
        ]);
    }

    /**
     * Cleanup after test
     */
    protected function tearDown(): void
    {
        // Database transaction will rollback automatically
        parent::tearDown();
    }

    /**
     * Refresh the test database before each test class
     */
    protected function refreshTestDatabase(): void
    {
        // Delete the test database file if it exists
        $database = database_path('database_test.sqlite');
        if (file_exists($database)) {
            unlink($database);
        }

        // Create fresh test database
        touch($database);
    }

    /**
     * Seed the test database with test data
     */
    protected function seed(): void
    {
        $this->artisan('db:seed', [
            '--database' => 'sqlite',
        ]);
    }

    /**
     * Seed specific seeder for test data
     */
    protected function seedDatabase(string $seederClass): void
    {
        $this->artisan('db:seed', [
            '--class' => $seederClass,
            '--database' => 'sqlite',
        ]);
    }

    /**
     * Assert that the test is running in test environment
     */
    protected function assertTestEnvironment(): void
    {
        $this->assertEquals('testing', config('app.env'), 
            'Tests must run in testing environment');
    }

    /**
     * Get test database name
     */
    protected function getTestDatabaseName(): string
    {
        return database_path('database_test.sqlite');
    }
}
