<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait for tracking and verifying database state changes and rollback
 * 
 * This trait provides utilities to:
 * - Track database changes during a test
 * - Verify proper rollback of transactions
 * - Validate state transitions
 * - Snapshot database state
 */
trait TracksDatabaseState
{
    /**
     * Database state snapshots
     */
    protected array $stateSnapshots = [];

    /**
     * Change log for tracking operations
     */
    protected array $changeLog = [];

    /**
     * Start tracking database changes
     * 
     * Creates a snapshot of the current state and begins logging changes
     */
    public function startTrackingChanges(string $snapshotName = 'default'): void
    {
        $this->stateSnapshots[$snapshotName] = [
            'timestamp' => microtime(true),
            'tables' => $this->captureTableStates(),
        ];

        $this->changeLog[$snapshotName] = [];
    }

    /**
     * Stop tracking and get the summary of changes
     */
    public function stopTrackingChanges(string $snapshotName = 'default'): array
    {
        $currentState = $this->captureTableStates();
        $previousSnapshot = $this->stateSnapshots[$snapshotName] ?? null;

        if (!$previousSnapshot) {
            return [];
        }

        $changes = [];
        foreach ($currentState as $table => $current) {
            $previous = $previousSnapshot['tables'][$table] ?? null;

            if ($previous !== $current) {
                $changes[$table] = [
                    'before' => $previous,
                    'after' => $current,
                    'difference' => $current['count'] - ($previous['count'] ?? 0),
                ];
            }
        }

        return $changes;
    }

    /**
     * Capture the current state of all tables
     */
    protected function captureTableStates(): array
    {
        $states = [];
        $tables = $this->getDatabaseTables();

        foreach ($tables as $table) {
            $states[$table] = [
                'count' => DB::table($table)->count(),
                'records' => DB::table($table)->get()->toArray(),
                'timestamp' => now(),
            ];
        }

        return $states;
    }

    /**
     * Log a change operation
     */
    public function logChange(string $snapshotName, string $operation, array $details = []): void
    {
        $this->changeLog[$snapshotName][] = [
            'operation' => $operation,
            'timestamp' => microtime(true),
            'details' => $details,
        ];
    }

    /**
     * Get the change log for a snapshot
     */
    public function getChangeLog(string $snapshotName = 'default'): array
    {
        return $this->changeLog[$snapshotName] ?? [];
    }

    /**
     * Verify that a specific change occurred
     */
    public function assertChangeOccurred(string $table, string $operation, string $snapshotName = 'default'): void
    {
        $changes = $this->stopTrackingChanges($snapshotName);

        $this->assertArrayHasKey(
            $table,
            $changes,
            "Expected changes in table {$table}, but none were found"
        );

        $tableChanges = $changes[$table];
        $this->assertIsArray($tableChanges, "Changes for {$table} should be tracked");
    }

    /**
     * Verify no changes were made to a table
     */
    public function assertNoChangesToTable(string $table, string $snapshotName = 'default'): void
    {
        $changes = $this->stopTrackingChanges($snapshotName);

        $this->assertArrayNotHasKey(
            $table,
            $changes,
            "Expected no changes to table {$table}, but changes were detected: " . json_encode($changes[$table] ?? [])
        );
    }

    /**
     * Verify row count changed for a table
     */
    public function assertRowCountChanged(string $table, int $expectedDifference, string $snapshotName = 'default'): void
    {
        $changes = $this->stopTrackingChanges($snapshotName);

        $this->assertArrayHasKey(
            $table,
            $changes,
            "Expected changes in table {$table}"
        );

        $this->assertEquals(
            $expectedDifference,
            $changes[$table]['difference'],
            "Expected row count difference of {$expectedDifference} in table {$table}, got {$changes[$table]['difference']}"
        );
    }

    /**
     * Print state snapshot for debugging
     */
    public function printStateSnapshot(string $snapshotName = 'default'): void
    {
        $snapshot = $this->stateSnapshots[$snapshotName] ?? null;

        if (!$snapshot) {
            echo "No snapshot found for {$snapshotName}\n";
            return;
        }

        echo "\n=== State Snapshot: {$snapshotName} ===\n";
        echo "Timestamp: {$snapshot['timestamp']}\n";
        echo "Tables State:\n";

        foreach ($snapshot['tables'] as $table => $state) {
            echo "  {$table}: {$state['count']} rows\n";
        }
    }

    /**
     * Print change log for debugging
     */
    public function printChangeLog(string $snapshotName = 'default'): void
    {
        $changes = $this->stopTrackingChanges($snapshotName);

        if (empty($changes)) {
            echo "No changes recorded for snapshot {$snapshotName}\n";
            return;
        }

        echo "\n=== Change Log: {$snapshotName} ===\n";

        foreach ($changes as $table => $change) {
            echo "Table: {$table}\n";
            echo "  Before: {$change['before']['count']} rows\n";
            echo "  After: {$change['after']['count']} rows\n";
            echo "  Difference: {$change['difference']}\n";
        }
    }

    /**
     * Simulate a test failure to verify rollback works
     * 
     * Use in tests to verify transactions rollback on failure
     */
    public function simulateFailureForRollbackTest(): void
    {
        $this->fail('Intentional failure to test rollback behavior');
    }
}