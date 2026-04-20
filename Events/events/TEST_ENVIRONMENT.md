# Test Environment & Isolation - Base de Test

## Overview

This document describes the test environment and database isolation setup implemented for the Events application. This ensures tests run in a clean, isolated environment without affecting production or other test runs.

## Architecture

### 1. **Environment Configuration**

#### `.env.testing` (New)
- Dedicated environment file for tests
- Uses SQLite test database: `database/database_test.sqlite`
- Array-based cache, sessions, and mail for test isolation
- All external services (Redis, Queue, Telescope) disabled

#### `phpunit.xml` (Updated)
- References the test database instead of in-memory
- Defines all test environment variables
- Enables proper test isolation across test runs

### 2. **Test Database Isolation**

**Previous Approach:** In-memory SQLite (`":memory:"`)
- ❌ Created fresh database each test
- ❌ Limited debugging capabilities
- ❌ No persistence between test runs for inspection

**New Approach:** File-based SQLite (`database/database_test.sqlite`)
- ✅ Fresh database before each test class (via `refreshTestDatabase()`)
- ✅ Consistent isolation with DatabaseTransactions trait
- ✅ Queryable for debugging
- ✅ Automatic rollback after each test

### 3. **Transaction-Based Rollback**

All test classes inherit from `Tests\TestCase` which uses `DatabaseTransactions` trait:

```php
// Each test runs inside a database transaction
// After the test completes, the transaction is automatically rolled back
// This ensures:
// - Clean slate for next test
// - No data persists between tests
// - Performance (no full database reset needed)
```

## Key Files

### Tests/TestCase.php (Enhanced)

New helper methods for test setup and isolation:

```php
// Verify test environment
$this->assertTestEnvironment();

// Manual database refresh (called automatically in setUp)
$this->refreshTestDatabase();

// Seed test data programmatically
$this->seed();
$this->seedDatabase('CategorySeeder');

// Get test database path
$database = $this->getTestDatabaseName();
```

### Database Factories

New factories for creating consistent test data:

- **EventFactory** (`database/factories/EventFactory.php`)
  - Generates realistic event test data
  - States: `published()`, `draft()`, `archived()`

- **CategoryFactory** (`database/factories/CategoryFactory.php`)
  - Generates test categories
  - Unique names to avoid conflicts

- **UserFactory** (existing)
  - Updated for test consistency

## Test Patterns

### Arrange-Act-Assert (AAA) Pattern

All updated tests follow the AAA pattern for clarity:

```php
public function test_it_can_create_an_event()
{
    // Arrange - Set up isolated test data
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $data = [
        'title' => 'New Test Event ' . uniqid(),
        'description' => 'Description',
        'event_date' => now()->addDays(10)->toDateTimeString(),
        'status' => 'draft',
        'categories' => []
    ];
    
    // Act - Perform the action
    $event = $this->service->createEvent($data, UploadedFile::fake()->create('event.jpg'));

    // Assert - Verify the result
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => $data['title']
    ]);
}
```

### Using Factories

Instead of relying on CSV data, use factories to create isolated test data:

```php
// Before (dependent on CSV data)
$event = Event::first();

// After (isolated test data)
$event = Event::factory()->create([
    'status' => 'published',
    'title' => 'Specific Title'
]);
```

### Unique Data Generation

Use `uniqid()` for test data uniqueness:

```php
Event::factory()->create([
    'title' => 'Unique Title ' . uniqid()
]);

Category::create([
    'name' => 'Test Category ' . uniqid()
]);
```

## Running Tests

### All Tests
```bash
php artisan test
```

### Specific Test File
```bash
php artisan test tests/Unit/EventServiceTest.php
```

### Specific Test Method
```bash
php artisan test tests/Unit/EventServiceTest.php --filter test_it_can_create_an_event
```

### With Verbose Output
```bash
php artisan test --verbose
```

### With Coverage Report
```bash
php artisan test --coverage
```

## Best Practices

### ✅ DO:

1. **Use Factories** - Create predictable test data with factories
   ```php
   $event = Event::factory()->create();
   ```

2. **Isolate Test Data** - Each test should be independent
   ```php
   // Good - creates fresh data for this test only
   Event::factory()->create(['status' => 'published']);
   ```

3. **Use Unique Identifiers** - Avoid conflicts between tests
   ```php
   'name' => 'Category ' . uniqid()
   ```

4. **Verify Environment** - Assert in test environment
   ```php
   $this->assertTestEnvironment();
   ```

5. **Use Fake Storage** - For file uploads
   ```php
   Storage::fake('public');
   $file = UploadedFile::fake()->create('image.jpg');
   ```

### ❌ DON'T:

1. **Don't Depend on CSV Data** - CSV data may change or be incomplete
   ```php
   // Bad - depends on CSV seeders
   $event = Event::first();
   ```

2. **Don't Share State** - Tests should be independent
   ```php
   // Bad - test A affects test B
   $event = Event::factory()->create();
   // other test expects specific count
   ```

3. **Don't Use Production Database** - Always use test isolation
   ```php
   // Bad - accidental production impact
   DB_CONNECTION=mysql in test
   ```

4. **Don't Commit to Global State** - Use transactions for automatic rollback
   ```php
   // Avoid - doesn't clean up
   // Good - automatically rolled back
   use DatabaseTransactions;
   ```

## Debugging Tests

### View Test Database

After running tests, inspect the database:

```bash
# SQLite CLI
sqlite3 database/database_test.sqlite

# View tables
.tables

# Query data
SELECT * FROM events;
```

### Debug Output

Add `dd()` or `dump()` in tests:

```php
public function test_something()
{
    $result = $this->service->doSomething();
    dd($result); // Dump and die
}
```

### Verbose Test Output

```bash
php artisan test --verbose
```

### Filter and Run Single Test

```bash
php artisan test --filter test_it_can_create_an_event
```

## Troubleshooting

### Issue: "No events found in database to test with"

**Cause:** Test depends on CSV seed data
**Solution:** Use factories to create test data

```php
// Instead of:
$event = Event::first();

// Use:
$event = Event::factory()->create();
```

### Issue: Tests pass individually but fail together

**Cause:** Tests are sharing state
**Solution:** Ensure each test creates isolated data

```php
// Add unique data in each test
$event = Event::factory()->create([
    'title' => 'Unique ' . uniqid()
]);
```

### Issue: Test database not cleaning up

**Cause:** DatabaseTransactions trait not used
**Solution:** Extend TestCase which includes the trait

```php
class MyTest extends TestCase // ✅ Includes DatabaseTransactions
{
    // ...
}
```

### Issue: Previous test data affecting current test

**Cause:** Transaction not being rolled back
**Solution:** Verify TestCase is properly extended

```php
// Verify in setUp()
$this->assertTestEnvironment();
$this->refreshTestDatabase();
```

## Related Files

- [tests/TestCase.php](tests/TestCase.php) - Base test class with isolation helpers
- [tests/Unit/EventServiceTest.php](tests/Unit/EventServiceTest.php) - Updated with factory pattern
- [tests/Unit/CategoryServiceTest.php](tests/Unit/CategoryServiceTest.php) - Updated with factory pattern
- [database/factories/EventFactory.php](database/factories/EventFactory.php) - Event factory
- [database/factories/CategoryFactory.php](database/factories/CategoryFactory.php) - Category factory
- [.env.testing](.env.testing) - Test environment configuration
- [phpunit.xml](phpunit.xml) - PHPUnit configuration

## Architecture Diagram

```
┌─────────────────────────────────────────────┐
│         Test Execution (phpunit)            │
└────────────────┬────────────────────────────┘
                 │
                 ↓
    ┌────────────────────────────┐
    │  Load .env.testing Config  │
    │  (APP_ENV=testing)         │
    └────────────┬───────────────┘
                 │
                 ↓
    ┌────────────────────────────────────────┐
    │  TestCase::setUp()                     │
    │  - Verify test environment             │
    │  - Refresh test database               │
    │  - Run migrations                      │
    │  - Start database transaction          │
    └────────────┬───────────────────────────┘
                 │
                 ↓
    ┌────────────────────────────────────────┐
    │  Create Test Data (Factories)          │
    │  - Event::factory()->create()          │
    │  - Category::factory()->create()       │
    └────────────┬───────────────────────────┘
                 │
                 ↓
    ┌────────────────────────────────────────┐
    │  Run Test Logic                        │
    │  (Arrange → Act → Assert)              │
    └────────────┬───────────────────────────┘
                 │
                 ↓
    ┌────────────────────────────────────────┐
    │  TestCase::tearDown()                  │
    │  - Rollback database transaction       │
    │  - Clean up resources                  │
    │  - Cleanup complete ✓                  │
    └────────────────────────────────────────┘
```

## Next Steps

1. **Add Feature Tests** - Create Feature tests for HTTP endpoints
2. **Expand Factories** - Add more factory states for various scenarios
3. **Add Integration Tests** - Test service interactions
4. **CI/CD Integration** - Run tests in GitHub Actions or similar
5. **Coverage Goals** - Aim for 80%+ code coverage

---

**Last Updated:** April 20, 2026
**Version:** 1.0
