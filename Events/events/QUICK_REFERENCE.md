# Quick Reference: Transactions & Rollback

## 🚀 Quick Start

```bash
# Run tests
php artisan test

# Run with details
php artisan test --verbose

# Run one test
php artisan test --filter test_name
```

## 📋 Test Template

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Traits\TracksDatabaseState;
use App\Models\Model;

class MyTest extends TestCase
{
    use TracksDatabaseState;  // ← Add this trait
    
    protected function setUp(): void
    {
        parent::setUp();  // ← Must call parent
        
        // ✓ Verify environment
        $this->assertTestEnvironment();
        $this->assertDatabaseInTransaction();
    }
    
    public function test_something()
    {
        // Track changes
        $this->startTrackingChanges('my_test');
        
        // Arrange
        $model = Model::factory()->create();
        
        // Act
        // ... do something ...
        
        // Assert
        $this->assertRowCountChanged('models', 1, 'my_test');
        // ✓ After test: ROLLBACK happens automatically
    }
}
```

## 🔍 Verification Methods

### Check Transaction Active
```php
$this->assertDatabaseInTransaction();
$level = $this->getCurrentTransactionLevel();
```

### Verify Environment
```php
$this->assertTestEnvironment();  // Must be 'testing'
```

### Track Changes
```php
$this->startTrackingChanges('operation');
// ... do something ...
$this->assertRowCountChanged('table_name', 5, 'operation');
```

### Debug State
```php
$this->printStateSnapshot('operation');
$this->printChangeLog('operation');
```

## 📊 State Tracking

```php
// Snapshot before operation
$this->startTrackingChanges('snapshot_name');

// Do something
Event::factory(5)->create();
Category::factory(2)->create();

// Verify changes
$changes = $this->stopTrackingChanges('snapshot_name');
// Result: {
//   'events': {'before': 0, 'after': 5, 'difference': 5},
//   'categories': {'before': 0, 'after': 2, 'difference': 2}
// }

// Assert specific changes
$this->assertRowCountChanged('events', 5, 'snapshot_name');
$this->assertRowCountChanged('categories', 2, 'snapshot_name');
$this->assertNoChangesToTable('users', 'snapshot_name');
```

## ✅ TestCase Methods

| Method | Purpose |
|--------|---------|
| `assertTestEnvironment()` | Verify APP_ENV=testing |
| `assertDatabaseInTransaction()` | Verify transaction active |
| `getCurrentTransactionLevel()` | Get nesting level |
| `getInitialRowCount($table)` | Get starting count |
| `assertTableResetToInitialState($table)` | Verify reset |
| `getDatabaseTables()` | List all tables |
| `getTestDatabaseName()` | Get test DB path |
| `getDatabaseConnection()` | Get connection name |

## ✅ TracksDatabaseState Methods

| Method | Purpose |
|--------|---------|
| `startTrackingChanges($name)` | Begin state snapshot |
| `stopTrackingChanges($name)` | Get changes |
| `assertRowCountChanged($table, $count, $name)` | Verify row count |
| `assertNoChangesToTable($table, $name)` | Verify no change |
| `assertChangeOccurred($table, $op, $name)` | Verify change |
| `getChangeLog($name)` | Get change history |
| `printStateSnapshot($name)` | Debug state |
| `printChangeLog($name)` | Debug changes |

## 🔄 Transaction Lifecycle

```
START
  ↓ setUp()
[Transaction Begins] ← Test isolation starts
  ↓
[Arrange Data]
  ↓
[Act - Run Code]
  ↓
[Assert Results]
  ↓ tearDown()
[ROLLBACK] ← All changes discarded ✓
  ↓
[Database Clean] ← Ready for next test ✓
  ↓
NEXT TEST
```

## 💾 Database State

```
Before Test:
┌─ events: 0 rows
├─ categories: 0 rows
├─ users: 0 rows
└─ ...

During Test (in transaction):
┌─ events: +5 rows
├─ categories: +2 rows
├─ users: +1 row
└─ ... (visible within test)

After Test (ROLLBACK):
┌─ events: 0 rows ✓
├─ categories: 0 rows ✓
├─ users: 0 rows ✓
└─ ... (clean again)
```

## 🎯 Common Patterns

### Pattern 1: Simple Create Test
```php
public function test_create()
{
    $this->startTrackingChanges('create');
    
    $model = Model::factory()->create();
    
    $this->assertRowCountChanged('models', 1, 'create');
}
```

### Pattern 2: Update Without Adding Rows
```php
public function test_update()
{
    $model = Model::factory()->create();
    
    $model->update(['name' => 'new']);
    
    // Still 1 row (not 2)
    $this->assertEquals(1, Model::count());
}
```

### Pattern 3: Verify Multiple Tables
```php
public function test_complex_operation()
{
    $this->startTrackingChanges('complex');
    
    $user = User::factory()->create();
    Event::factory(3)->create();
    Category::factory(2)->create();
    
    $this->assertRowCountChanged('users', 1, 'complex');
    $this->assertRowCountChanged('events', 3, 'complex');
    $this->assertRowCountChanged('categories', 2, 'complex');
}
```

### Pattern 4: Verify No Unintended Changes
```php
public function test_isolated_operation()
{
    $this->startTrackingChanges('isolated');
    
    Event::factory(5)->create();
    
    // Verify only events changed
    $this->assertRowCountChanged('events', 5, 'isolated');
    $this->assertNoChangesToTable('categories', 'isolated');
    $this->assertNoChangesToTable('users', 'isolated');
}
```

## ⚠️ Common Mistakes

### ❌ NOT calling parent::setUp()
```php
// BAD - breaks transaction
protected function setUp(): void
{
    $this->service = new Service();  // Not calling parent!
}

// GOOD
protected function setUp(): void
{
    parent::setUp();  // ← Must do this
    $this->service = new Service();
}
```

### ❌ NOT calling parent::tearDown()
```php
// BAD - breaks rollback
protected function tearDown(): void
{
    $this->cleanup();  // Not calling parent!
}

// GOOD
protected function tearDown(): void
{
    $this->cleanup();
    parent::tearDown();  // ← Enables rollback
}
```

### ❌ Using previous test's data
```php
// BAD - data was rolled back
public function test_two()
{
    $event = Event::first();  // NULL! Test_one's data was rolled back
}
```

### ❌ Manually committing
```php
// BAD - prevents rollback
DB::commit();  // Don't do this in tests!

// GOOD - let trait handle it
// Just use DB normally, trait handles transaction
```

## 📈 Performance

| Metric | Value |
|--------|-------|
| Per-test transaction cost | ~1-5ms |
| Rollback time | <5ms |
| Full test suite (10 tests) | <50ms |
| **Faster than:** | Database recreation (50-200ms) |

## 🐛 Debugging

```php
// Print what changed
$this->printChangeLog('my_operation');

// Print state snapshot
$this->printStateSnapshot('my_snapshot');

// Check transaction level
echo "Level: " . $this->getCurrentTransactionLevel();

// Verify isolation
$this->assertDatabaseInTransaction();

// Check if changes were detected
$changes = $this->stopTrackingChanges('operation');
var_dump($changes);
```

## 🔗 Documentation

- **Full Details:** [TRANSACTIONS_ROLLBACK.md](TRANSACTIONS_ROLLBACK.md)
- **Practical Guide:** [TESTING_GUIDE.md](TESTING_GUIDE.md)
- **Environment Setup:** [TEST_ENVIRONMENT.md](TEST_ENVIRONMENT.md)
- **Implementation Summary:** [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)

## ✨ Key Points

✅ **Automatic** - ROLLBACK happens without you doing anything  
✅ **Fast** - ~1-5ms per test, no database recreation  
✅ **Isolated** - Each test is completely independent  
✅ **Debuggable** - Database persists for inspection  
✅ **Reliable** - Atomic transactions, no partial states  
✅ **Simple** - Just extend TestCase and use trait  

---

**Remember: Your test database is automatically cleaned after each test! 🎉**
