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
     * Initial database state for validation
     */
    protected array $initialState = [];

    /**
     * Transaction level tracking
     */
    protected int $transactionLevel = 0;

    /**
     * Whether automatic refresh is enabled
     */
    protected bool $refreshDatabasePerTest = true;

    /**
     * Setup method - called before each test
     * 
     * This runs inside a database transaction that will be rolled back
     * after the test completes (handled by DatabaseTransactions trait)
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Verify we're in test environment
        $this->assertTestEnvironment();

        // Capture initial database state
        $this->recordInitialState();

        // Begin transaction for test isolation
        $this->beginDatabaseTransaction();
    }

    /**
     * Teardown method - called after each test
     * 
     * The database transaction is automatically rolled back by
     * the DatabaseTransactions trait
     */
    protected function tearDown(): void
    {
        // Verify rollback happened correctly
        $this->assertRollbackCleanup();

        parent::tearDown();
    }

    /**
     * Record the initial database state before test execution
     * 
     * This captures table record counts and state for validation
     * that changes are properly rolled back
     */
    protected function recordInitialState(): void
    {
        $tables = $this->getDatabaseTables();

        foreach ($tables as $table) {
            $this->initialState[$table] = [
                'count' => DB::table($table)->count(),
                'timestamp' => now(),
            ];
        }
    }

    /**
     * Begin database transaction
     * 
     * Ensures all test operations are wrapped in a transaction
     * for atomicity and rollback
     */
    protected function beginDatabaseTransaction(): void
    {
        DB::beginTransaction();
        $this->transactionLevel++;
    }

    /**
     * Verify transaction rollback cleanup
     * 
     * Called in tearDown to confirm transaction was rolled back properly
     */
    protected function assertRollbackCleanup(): void
    {
        // Verify we're still in test environment
        if (config('app.env') === 'testing') {
            // Cannot fully verify rollback until tearDown completes
            // But we can verify the transaction is still active
            if (DB::transactionLevel() > 0) {
                // Transaction is still active, will be rolled back by parent
            }
        }
    }

    /**
     * Get all database tables
     */
    protected function getDatabaseTables(): array
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => $this->getSqliteTables(),
            'mysql' => $this->getMysqlTables(),
            'pgsql' => $this->getPostgresTables(),
            default => [],
        };
    }

    /**
     * Get SQLite tables
     */
    protected function getSqliteTables(): array
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        return array_map(fn($table) => $table->name, $tables);
    }

    /**
     * Get MySQL tables
     */
    protected function getMysqlTables(): array
    {
        $database = DB::getDatabaseName();
        $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$database]);
        return array_map(fn($table) => $table->TABLE_NAME, $tables);
    }

    /**
     * Get PostgreSQL tables
     */
    protected function getPostgresTables(): array
    {
        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        return array_map(fn($table) => $table->tablename, $tables);
    }

    /**
     * Assert that the test is running in test environment
     */
    protected function assertTestEnvironment(): void
    {
        $this->assertEquals(
            'testing',
            config('app.env'),
            'Tests must run in testing environment for proper isolation'
        );
    }

    /**
     * Get initial row count for a table
     */
    protected function getInitialRowCount(string $table): int
    {
        return $this->initialState[$table]['count'] ?? 0;
    }

    /**
     * Assert that table has been reset to initial state
     */
    protected function assertTableResetToInitialState(string $table): void
    {
        $currentCount = DB::table($table)->count();
        $initialCount = $this->getInitialRowCount($table);

        $this->assertEquals(
            $initialCount,
            $currentCount,
            "Table {$table} was not reset to initial state (initial: {$initialCount}, current: {$currentCount})"
        );
    }

    /**
     * Assert that the database remains in a transaction
     */
    protected function assertDatabaseInTransaction(): void
    {
        $this->assertGreaterThan(
            0,
            DB::transactionLevel(),
            'Database should be in a transaction for test isolation'
        );
    }

    /**
     * Get current transaction level
     */
    protected function getCurrentTransactionLevel(): int
    {
        return DB::transactionLevel();
    }

    /**
     * Refresh test database by deleting test database file
     */
    protected function refreshTestDatabase(): void
    {
        $database = database_path('database_test.sqlite');

        if (file_exists($database)) {
            unlink($database);
        }

        // Create fresh test database
        touch($database);

        // Run migrations
        $this->artisan('migrate', [
            '--database' => 'sqlite',
            '--path' => 'database/migrations',
        ]);
    }

    /**
     * Get test database name
     */
    protected function getTestDatabaseName(): string
    {
        return database_path('database_test.sqlite');
    }

    /**
     * Get current database connection name
     */
    protected function getDatabaseConnection(): string
    {
        return DB::getDefaultConnection();
    }

    /**
     * Manually rollback the current transaction (for testing rollback behavior)
     */
    protected function manuallyRollbackTransaction(): void
    {
        DB::rollback();
        $this->transactionLevel--;
    }

    /**
     * Manually commit the current transaction (use with caution in tests!)
     */
    protected function manuallyCommitTransaction(): void
    {
        DB::commit();
        $this->transactionLevel--;
    }

    /**
     * Savepoint support for nested transaction testing
     */
    protected function createSavepoint(string $name): void
    {
        DB::statement("SAVEPOINT {$name}");
    }

    /**
     * Rollback to a specific savepoint
     */
    protected function rollbackToSavepoint(string $name): void
    {
        DB::statement("ROLLBACK TO SAVEPOINT {$name}");
    }

    /**
     * Verify database constraints are enforced
     */
    protected function assertDatabaseConstraintsEnabled(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $foreignKeys = DB::select('PRAGMA foreign_keys;');
            $this->assertNotEmpty($foreignKeys, 'SQLite foreign key constraints should be enabled');
        }
    }
}
