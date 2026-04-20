# Guide Pratique: Tests avec Transactions et Rollback

## Démarrage Rapide

### Installation et Configuration

```bash
# 1. Ensure test environment is configured
cat .env.testing

# 2. Run migrations for testing
php artisan migrate --env=testing

# 3. Verify test database exists
ls -la database/database_test.sqlite
```

### Exécuter les Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/EventServiceTest.php

# Run specific test method
php artisan test --filter test_it_can_create_an_event

# Run with verbose output (see transaction details)
php artisan test --verbose

# Run with detailed output
php artisan test -v

# Run with coverage report
php artisan test --coverage
```

## Examples Pratiques

### Example 1: Simple Test avec Transactions

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;

class SimpleTransactionTest extends TestCase
{
    public function test_database_transaction_isolation()
    {
        // ✓ Inside transaction
        $this->assertDatabaseInTransaction();
        
        // Get count before
        $countBefore = Event::count();
        
        // Create event (inside transaction)
        Event::factory()->create(['title' => 'Test Event']);
        
        // Verify it was created in transaction
        $this->assertEquals($countBefore + 1, Event::count());
    }
}

// After test completes:
// → tearDown() called
// → ROLLBACK executed
// → All changes discarded
// → Next test starts with clean database
```

### Example 2: État Initial Tracking

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Traits\TracksDatabaseState;
use App\Models\Event;
use App\Models\Category;

class StateTrackingTest extends TestCase
{
    use TracksDatabaseState;
    
    public function test_track_state_changes()
    {
        // Start recording state
        $this->startTrackingChanges('bulk_create');
        
        // Create events
        Event::factory(5)->create();
        Category::factory(3)->create();
        
        // Stop and get changes
        $changes = $this->stopTrackingChanges('bulk_create');
        
        // Verify specific changes
        $this->assertRowCountChanged('events', 5, 'bulk_create');
        $this->assertRowCountChanged('categories', 3, 'bulk_create');
        
        // Print for debugging
        $this->printChangeLog('bulk_create');
    }
}
```

### Example 3: Validating Rollback Works

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;

class RollbackValidationTest extends TestCase
{
    public function test_create_with_changes()
    {
        // Ensure we're in transaction
        $this->assertDatabaseInTransaction();
        
        // Count before
        $before = Event::count();
        
        // Make changes in transaction
        Event::factory(10)->create();
        $after = Event::count();
        
        // Verify changes are visible within transaction
        $this->assertEquals($before + 10, $after);
        
        // When test ends:
        // → tearDown() runs
        // → ROLLBACK happens (all 10 events removed)
        // → Next test sees $before count
    }
    
    public function test_next_test_has_clean_slate()
    {
        // The 10 events from test_create_with_changes are gone
        // due to automatic ROLLBACK in previous test's tearDown()
        $count = Event::count();
        
        // This should be the same as before count in previous test
        $this->assertEquals(0, $count);
    }
}
```

### Example 4: Testing Service with Transaction Verification

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Traits\TracksDatabaseState;
use App\Models\Event;
use App\Models\User;
use App\Services\EventService;

class EventServiceTransactionTest extends TestCase
{
    use TracksDatabaseState;
    
    protected EventService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Verify isolation
        $this->assertTestEnvironment();
        $this->assertDatabaseInTransaction();
        
        $this->service = new EventService();
    }
    
    public function test_create_event_with_transaction_verification()
    {
        // Track the operation
        $this->startTrackingChanges('create_operation');
        
        // Create user (within transaction)
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create event via service
        $event = $this->service->createEvent([
            'title' => 'New Event',
            'description' => 'Description',
            'event_date' => now()->addDays(5)->toDateTimeString(),
            'status' => 'draft',
            'categories' => []
        ], null);
        
        // Verify in transaction
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'New Event'
        ]);
        
        // Verify state changed
        $this->assertRowCountChanged('events', 1, 'create_operation');
        $this->assertRowCountChanged('users', 1, 'create_operation');
    }
    
    public function test_update_without_adding_rows()
    {
        // Track the operation
        $this->startTrackingChanges('update_operation');
        
        // Create initial event
        $event = Event::factory()->create();
        
        // Update via service (should NOT add new rows to events)
        $this->service->updateEvent($event, [
            'title' => 'Updated Title'
        ]);
        
        // Verify update happened
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title'
        ]);
        
        // Verify no rows added (just update)
        // Only one row should exist (from factory, not from update)
        $changes = $this->stopTrackingChanges('update_operation');
        $this->assertEquals(1, $changes['events']['difference']);
    }
}
```

## Vérifier que le Rollback Fonctionne

### Méthode 1: Inspecter la Base de Données

```bash
# While test is running, check database in another terminal
sqlite3 database/database_test.sqlite

# Inside SQLite:
sqlite> SELECT COUNT(*) FROM events;
10  # Events created in test

# After test completes:
sqlite> SELECT COUNT(*) FROM events;
0   # All rolled back!
```

### Méthode 2: Log des Changements

```php
public function test_verify_rollback_with_logging()
{
    $this->startTrackingChanges('verify_rollback');
    
    // Initial state
    echo "\nBefore: " . Event::count() . " events\n";
    
    // Create events
    Event::factory(5)->create();
    echo "After create: " . Event::count() . " events\n";
    
    // Print changes log
    $this->printChangeLog('verify_rollback');
    // Output:
    // === Change Log: verify_rollback ===
    // Table: events
    //   Before: 0 rows
    //   After: 5 rows
    //   Difference: 5
}

public function test_rollback_verification_next_test()
{
    // The 5 events are gone due to rollback
    $count = Event::count();
    echo "\nAfter rollback: " . $count . " events\n";
    // Output: After rollback: 0 events
    
    $this->assertEquals(0, $count);
}
```

### Méthode 3: Transaction Level Monitoring

```php
public function test_monitor_transaction_level()
{
    // Check transaction is active
    $level = $this->getCurrentTransactionLevel();
    echo "\nTransaction level: " . $level . "\n";  // Should be 1+
    
    $this->assertGreaterThan(0, $level, 
        'Should be inside a transaction');
    
    // Create some data
    Event::factory(3)->create();
    
    // Level is still the same (no nested transactions started)
    $this->assertEquals($level, $this->getCurrentTransactionLevel());
}
```

## Debugging Tests

### Afficher l'État de la Base de Données

```php
public function test_debug_database_state()
{
    $this->startTrackingChanges('debug');
    
    // Do some operations
    Event::factory(5)->create();
    Category::factory(2)->create();
    
    // Print state for debugging
    $this->printStateSnapshot('debug');
    // Output:
    // === State Snapshot: debug ===
    // Timestamp: 1713607200.5234
    // Tables State:
    //   events: 5 rows
    //   categories: 2 rows
    //   users: 0 rows
    
    $this->printChangeLog('debug');
    // Output:
    // === Change Log: debug ===
    // Table: events
    //   Before: 0 rows
    //   After: 5 rows
    //   Difference: 5
    // Table: categories
    //   Before: 0 rows
    //   After: 2 rows
    //   Difference: 2
}
```

### Tester le Comportement du Rollback

```php
public function test_rollback_on_assertion_failure()
{
    // Create events in transaction
    Event::factory(5)->create();
    
    // Make assertion that could fail
    // (PHPUnit catches it and still calls tearDown)
    // $this->fail('Intentional failure');
    
    // Even if test fails:
    // → tearDown() is called
    // → ROLLBACK happens
    // → Database is clean for next test
}

public function test_after_failed_test()
{
    // Previous test failed, but its changes were rolled back
    $count = Event::count();
    $this->assertEquals(0, $count);  // ✓ Should be 0
}
```

## Patterns Avancés

### Pattern 1: Multiple Snapshots

```php
public function test_multiple_operations_with_snapshots()
{
    // Snapshot 1: Before first operation
    $this->startTrackingChanges('before_batch1');
    Event::factory(5)->create();
    
    // Verify first batch
    $batch1_changes = $this->stopTrackingChanges('before_batch1');
    $this->assertRowCountChanged('events', 5, 'before_batch1');
    
    // Snapshot 2: Before second operation
    $this->startTrackingChanges('before_batch2');
    Event::factory(3)->create();
    
    // Verify second batch
    $batch2_changes = $this->stopTrackingChanges('before_batch2');
    $this->assertRowCountChanged('events', 3, 'before_batch2');
    
    // Total events should be 8
    $this->assertEquals(8, Event::count());
}
```

### Pattern 2: Verifying No Unintended Changes

```php
public function test_operation_affects_only_target_table()
{
    $this->startTrackingChanges('targeted_op');
    
    // Initial state
    $this->assertEquals(0, Event::count());
    $this->assertEquals(0, Category::count());
    
    // Only create events
    Event::factory(5)->create();
    
    // Verify only events changed
    $this->assertRowCountChanged('events', 5, 'targeted_op');
    $this->assertNoChangesToTable('categories', 'targeted_op');
    $this->assertNoChangesToTable('users', 'targeted_op');
}
```

### Pattern 3: Verification After Complex Operations

```php
public function test_complex_operation_verification()
{
    $this->startTrackingChanges('complex');
    
    // Create user
    $user = User::factory()->create();
    
    // Create events for user
    Event::factory(3)->create(['user_id' => $user->id]);
    
    // Attach categories
    $events = Event::all();
    $categories = Category::factory(2)->create();
    foreach ($events as $event) {
        $event->categories()->attach($categories);
    }
    
    // Verify all changes
    $this->assertRowCountChanged('users', 1, 'complex');
    $this->assertRowCountChanged('events', 3, 'complex');
    $this->assertRowCountChanged('categories', 2, 'complex');
    // Pivot table also changed
    $this->assertRowCountChanged('category_event', 6, 'complex');  // 3 events × 2 categories
    
    // Print detailed log
    $this->printChangeLog('complex');
}
```

## Commandes Utiles

```bash
# Run tests and see all output
php artisan test --verbose

# Run tests with coverage percentage
php artisan test --coverage

# Run specific test with detailed output
php artisan test tests/Unit/EventServiceTest.php::test_it_can_create_an_event -v

# Run tests matching a pattern
php artisan test --filter "transaction"

# Run tests but stop on first failure
php artisan test --stop-on-failure

# Run tests in parallel (faster)
php artisan test --parallel

# Run tests with specific number of processes
php artisan test --parallel --processes=4
```

## Checklist de Vérification

- [ ] Tests run with `php artisan test`
- [ ] All tests pass
- [ ] Transaction isolation works (verify with debug output)
- [ ] Rollback happens automatically
- [ ] Database is clean between tests
- [ ] No test data pollution
- [ ] Coverage report shows good coverage
- [ ] Tests run in ~1-5 seconds (not slow)

## Troubleshooting

### Problem: Tests take too long

**Cause:** Might not be using transactions
**Solution:** Verify TestCase extends Test with DatabaseTransactions

```php
class MyTest extends TestCase  // ✅ Has DatabaseTransactions
```

### Problem: Data from test_one appears in test_two

**Cause:** Rollback not happening
**Solution:** Check tearDown() is not overridden without calling parent

```php
protected function tearDown(): void
{
    parent::tearDown();  // ✅ Must call parent
}
```

### Problem: Cannot see database changes

**Cause:** Changes are in transaction, not on disk yet
**Solution:** Use TracksDatabaseState trait to see changes within transaction

```php
$this->startTrackingChanges('operation');
$this->printChangeLog('operation');  // See changes
```

### Problem: Foreign key constraint errors

**Cause:** Constraints enabled but dependencies missing
**Solution:** Create dependent records first, or disable constraints

```php
// ✅ Good - create in correct order
$user = User::factory()->create();
Event::factory()->create(['user_id' => $user->id]);

// ✓ Or disable for test if needed
DB::statement('PRAGMA foreign_keys = OFF;');
```

## Performance Tips

**Current setup is already optimized:**
- ✅ Uses transactions (fast rollback)
- ✅ No unnecessary database recreations
- ✅ Parallel execution ready
- ✅ Test database is file-based for debugging

**To make tests even faster:**
```bash
# Run in parallel (4 processes)
php artisan test --parallel --processes=4

# Only run changed tests (with proper setup)
php artisan test --only-changed
```

## Resources

- [TEST_ENVIRONMENT.md](TEST_ENVIRONMENT.md) - Environment isolation setup
- [TRANSACTIONS_ROLLBACK.md](TRANSACTIONS_ROLLBACK.md) - Detailed transaction documentation
- [tests/TestCase.php](tests/TestCase.php) - Enhanced test base class
- [tests/Traits/TracksDatabaseState.php](tests/Traits/TracksDatabaseState.php) - State tracking trait

---

**Last Updated:** April 20, 2026
**Version:** 1.0
