# Transactions et État Initial (Rollback)

## Overview

This document explains the database transaction and automatic rollback system that ensures complete isolation between tests. Each test runs in its own database transaction that is automatically rolled back after execution, guaranteeing a clean slate for the next test.

## Architecture

### Transaction Flow

```
Test Start
    ↓
setUp() - Record Initial State
    ↓
DB::beginTransaction() - Start isolated transaction
    ↓
Test Execution (Arrange → Act → Assert)
    ↓
All database changes made WITHIN this transaction
    ↓
tearDown() - Verify transaction state
    ↓
DatabaseTransactions trait automatically ROLLS BACK
    ↓
All changes DISCARDED ✓
    ↓
Test Complete - Database restored to initial state
```

## Key Components

### 1. DatabaseTransactions Trait (Laravel Built-in)

Located in: `Illuminate\Foundation\Testing\DatabaseTransactions`

**What it does:**
- Wraps each test in a database transaction
- Automatically rolls back after the test completes
- Handles nested transactions across test frameworks

**Why we use it:**
- Ensures atomicity - all or nothing
- Fast - no need to delete and recreate database
- Safe - no test data pollution
- Works with all database drivers

### 2. Enhanced TestCase Class

**New Methods:**

#### `recordInitialState()`
Captures the initial database state before test execution:
```php
// Stores table counts
$this->initialState = [
    'events' => ['count' => 0, 'timestamp' => now()],
    'categories' => ['count' => 0, 'timestamp' => now()],
    // ... all tables
];
```

#### `beginDatabaseTransaction()`
Explicitly starts the transaction:
```php
DB::beginTransaction();
$this->transactionLevel++;
```

#### `assertDatabaseInTransaction()`
Verifies we're inside an active transaction:
```php
$this->assertGreaterThan(0, DB::transactionLevel());
```

#### `assertTestEnvironment()`
Ensures we're in test environment:
```php
$this->assertEquals('testing', config('app.env'));
```

#### `getCurrentTransactionLevel()`
Returns the nesting level of transactions:
```php
$level = $this->getCurrentTransactionLevel(); // Returns 1, 2, 3, etc.
```

### 3. TracksDatabaseState Trait

**Location:** `tests/Traits/TracksDatabaseState.php`

**Features:**

#### `startTrackingChanges($name)`
Captures a snapshot before test operation:
```php
$this->startTrackingChanges('create_event_test');
Event::factory()->create();
```

#### `stopTrackingChanges($name)`
Compares current state to snapshot:
```php
$changes = $this->stopTrackingChanges('create_event_test');
// Returns: ['events' => ['count' => 10, 'before' => 9, 'after' => 10, 'difference' => 1]]
```

#### `assertRowCountChanged($table, $count, $name)`
Verifies specific table row count change:
```php
$this->assertRowCountChanged('events', 1, 'create_event_test');
// Asserts that exactly 1 row was added to events table
```

#### `assertNoChangesToTable($table, $name)`
Verifies a table wasn't modified:
```php
$this->assertNoChangesToTable('users', 'read_only_test');
```

## Test Execution Flow

### Before Test (setUp)

```php
protected function setUp(): void
{
    parent::setUp();  // BaseTestCase setup
    
    // 1. Assert test environment
    $this->assertTestEnvironment();  // Verify APP_ENV=testing
    
    // 2. Record initial state
    $this->recordInitialState();  // Capture table counts
    
    // 3. Begin transaction
    $this->beginDatabaseTransaction();  // Start DB transaction
}
```

### During Test

```php
public function test_it_can_create_an_event()
{
    // Arrange - Create test data (INSIDE TRANSACTION)
    $this->startTrackingChanges('create_event');
    $user = User::factory()->create();  // ← In transaction
    
    // Act
    $event = $this->service->createEvent($data);  // ← In transaction
    
    // Assert - Verify using snapshots
    $this->assertRowCountChanged('events', 1, 'create_event');
    // Changes are WITHIN the transaction, not persisted to disk yet
}
```

### After Test (tearDown)

```php
protected function tearDown(): void
{
    // Verify transaction state
    $this->assertRollbackCleanup();
    
    parent::tearDown();  // Calls DatabaseTransactions::tearDown()
}
```

**In Parent tearDown (DatabaseTransactions):**
```
1. Detect active transaction
2. ROLLBACK all changes
3. ✓ All database modifications DISCARDED
4. ✓ Database restored to initial state
```

## Transaction Isolation Guarantees

### ✅ Complete Isolation

Each test is independent:

```php
public function test_one()
{
    $event = Event::factory()->create(['title' => 'Event 1']);
    $this->assertEquals(1, Event::count());
}

public function test_two()
{
    // Event from test_one is NOT here due to rollback
    $this->assertEquals(0, Event::count());
    
    $event = Event::factory()->create(['title' => 'Event 2']);
    $this->assertEquals(1, Event::count());
}

public function test_three()
{
    // Events from test_one and test_two are NOT here
    $this->assertEquals(0, Event::count());
}
```

### ✅ No Test Pollution

Tests don't affect each other:

```
Test A: Creates 5 events
Test B: Creates 3 categories
Test C: Deletes 2 events

→ After each test, database rolls back
→ No residual data between tests
```

### ✅ Atomicity

All-or-nothing transactions:

```php
public function test_transaction_atomicity()
{
    // All these happen in ONE transaction
    User::factory()->create();        // Inside transaction
    Event::factory()->create();       // Inside transaction
    Category::factory()->create();    // Inside transaction
    
    // If anything fails or test ends:
    // → ALL changes rolled back together
    // → Never partial state on disk
}
```

## Rollback Behavior Examples

### Example 1: Successful Test with Changes

```php
public function test_create_event()
{
    // Before test: events table has 0 rows
    $this->assertEquals(0, Event::count());
    
    // During test (in transaction)
    Event::factory()->create();
    $this->assertEquals(1, Event::count());  // ✓ Passes
    
    // After tearDown() - ROLLBACK happens
    // Disk state: events table has 0 rows
}
```

### Example 2: Failed Assertion with Rollback

```php
public function test_failing_assertion()
{
    // During test (in transaction)
    $event = Event::factory()->create();
    
    // Bad assertion - will fail
    $this->assertEquals('WrongStatus', $event->status);  // ✗ Fails
    
    // Even though assertion failed:
    // → Even though assertions failed tearDown() still runs
    // → ROLLBACK still happens
    // Disk state: events table unchanged
}
```

### Example 3: Exception During Test

```php
public function test_exception_in_test()
{
    // During test (in transaction)
    Event::factory()->create();
    throw new Exception("Simulated error");  // Exception thrown
    
    // Even with exception:
    // → tearDown() still runs
    // → ROLLBACK happens automatically
    // → Exception captured by PHPUnit
    // Disk state: events table unchanged
}
```

## Database State Tracking

### Using Snapshots

```php
public function test_with_snapshots()
{
    // Capture initial state
    $this->startTrackingChanges('operation1');
    
    // Do operation
    Event::factory(5)->create();
    Category::factory(3)->create();
    
    // Check changes
    $changes = $this->stopTrackingChanges('operation1');
    
    // $changes = [
    //     'events' => ['before' => 0, 'after' => 5, 'difference' => 5],
    //     'categories' => ['before' => 0, 'after' => 3, 'difference' => 3],
    // ]
    
    // Assert specific changes
    $this->assertRowCountChanged('events', 5, 'operation1');
    $this->assertRowCountChanged('categories', 3, 'operation1');
}
```

### Debugging State Changes

```php
public function test_debug_state_changes()
{
    $this->startTrackingChanges('debug_test');
    
    // Do something
    Event::factory()->create(['title' => 'Test Event']);
    
    // Print state for debugging
    $this->printChangeLog('debug_test');
    
    // Output:
    // === Change Log: debug_test ===
    // Table: events
    //   Before: 0 rows
    //   After: 1 rows
    //   Difference: 1
}
```

## Best Practices

### ✅ DO:

1. **Verify Transaction State**
   ```php
   $this->assertDatabaseInTransaction();
   ```

2. **Track Changes for Complex Operations**
   ```php
   $this->startTrackingChanges('complex_op');
   // ... operations ...
   $this->assertRowCountChanged('events', 3, 'complex_op');
   ```

3. **Verify Test Environment**
   ```php
   $this->assertTestEnvironment();
   ```

4. **Use Snapshots for Assertions**
   ```php
   $this->startTrackingChanges('my_test');
   $this->assertChangeOccurred('events', 'create', 'my_test');
   ```

### ❌ DON'T:

1. **Don't Manually Commit**
   ```php
   // BAD - will persist to disk
   DB::commit();
   ```

2. **Don't Disable the Trait**
   ```php
   // BAD - won't use DatabaseTransactions
   // This will pollute database
   ```

3. **Don't Depend on Previous Test Data**
   ```php
   // BAD - will be rolled back
   $lastEventFromPreviousTest = Event::first();  // Null!
   ```

4. **Don't Skip Verification**
   ```php
   // BAD - you won't know if rollback failed
   // GOOD
   $this->assertTestEnvironment();
   $this->assertDatabaseInTransaction();
   ```

## Testing Rollback Behavior

### Verify Isolation Test

```php
public function test_transaction_isolation()
{
    // Count before
    $countBefore = DB::table('events')->count();
    
    // Create within transaction
    Event::factory(3)->create();
    $countDuring = DB::table('events')->count();
    
    // Verify changes within transaction
    $this->assertEquals($countBefore + 3, $countDuring);
}

public function test_isolation_next_test()
{
    // When this test runs, previous test's changes are rolled back
    $count = DB::table('events')->count();
    $this->assertEquals(0, $count);  // ✓ Should be 0
}
```

### Force Rollback Test

```php
public function test_rollback_on_failure()
{
    $this->startTrackingChanges('rollback_test');
    
    // Create events
    Event::factory(5)->create();
    
    // Intentionally fail to test rollback
    // (In real testing, PHPUnit catches exceptions and calls tearDown)
    // throw new Exception("Test failure");
    
    // tearDown will be called anyway
    // ROLLBACK will execute
    // Next test will have clean database
}
```

## Transaction Levels

### Understanding Nesting

```php
$this->transactionLevel = 0;

setUp() {
    DB::beginTransaction();
    $this->transactionLevel = 1;  // Level 1: Main transaction
}

// Inside test with savepoints:
$this->createSavepoint('sp1');  // Level 2: Savepoint

// Multiple nesting:
$this->getCurrentTransactionLevel();  // Returns: 2, 3, 4, etc
```

### Savepoint Support

```php
public function test_savepoint_rollback()
{
    // Create initial record
    Event::factory()->create();
    
    // Create savepoint
    $this->createSavepoint('save1');
    $this->assertEquals(1, Event::count());
    
    // Make more changes
    Event::factory()->create();
    $this->assertEquals(2, Event::count());
    
    // Rollback to savepoint
    $this->rollbackToSavepoint('save1');
    $this->assertEquals(1, Event::count());  // Restored
}
```

## Debugging Failed Rollbacks

### Check Transaction Level

```php
$level = $this->getCurrentTransactionLevel();
$this->assertGreaterThan(0, $level, 
    'Transaction should be active');
```

### Verify Database Connection

```php
$connection = $this->getDatabaseConnection();
$this->assertEquals('sqlite', $connection);
```

### Inspect Database Constraints

```php
$this->assertDatabaseConstraintsEnabled();
```

### Print State for Debugging

```php
$this->printStateSnapshot('debug');
$this->printChangeLog('debug');
```

## Related Files

- [tests/TestCase.php](tests/TestCase.php) - Enhanced with transaction management
- [tests/Traits/TracksDatabaseState.php](tests/Traits/TracksDatabaseState.php) - State tracking trait
- [tests/Unit/EventServiceTest.php](tests/Unit/EventServiceTest.php) - Transaction examples
- [tests/Unit/CategoryServiceTest.php](tests/Unit/CategoryServiceTest.php) - Transaction examples
- [phpunit.xml](phpunit.xml) - PHPUnit configuration

## Performance Implications

### Transaction Rollback vs Database Reset

| Method | Time | Way |
|--------|-------|-----|
| **Transaction Rollback** (Current) | ~1-5ms | Atomic, no disk I/O |
| **Delete & Recreate** | ~50-200ms | Full database reset |
| **In-Memory DB** | ~0ms | But can't debug, limited DB features |

**Current approach (Transaction Rollback):**
- ✅ Fast - no disk operations
- ✅ Debuggable - file persists until next test
- ✅ Feature-complete - all DB features work
- ✅ Safe - atomic operations

## Troubleshooting

### Issue: Changes not rolling back

**Cause:** Not using DatabaseTransactions trait
**Solution:** Extend TestCase which includes the trait

```php
class MyTest extends TestCase  // ✅ Includes DatabaseTransactions
```

### Issue: "Transaction level 0" error

**Cause:** Transaction already rolled back
**Solution:** Verify setUp() is calling parent::setUp()

```php
protected function setUp(): void
{
    parent::setUp();  // ✅ Must call parent
}
```

### Issue: Test data persisting between tests

**Cause:** Commits happening instead of rollback
**Solution:** Check for manual DB::commit() calls

```php
// ❌ BAD - prevents rollback
if ($condition) {
    DB::commit();
}

// ✅ GOOD - let trait handle it
// Don't call commit in tests
```

## Summary

**Transactions et État Initial (Rollback)** ensures:

1. ✅ **Atomicity** - All changes in one transaction
2. ✅ **Isolation** - No test pollution  
3. ✅ **Consistency** - Initial state preserved
4. ✅ **Durability** - Changes can be inspected before rollback
5. ✅ **Performance** - Fast rollback, no recreate needed

---

**Last Updated:** April 20, 2026
**Version:** 2.0
