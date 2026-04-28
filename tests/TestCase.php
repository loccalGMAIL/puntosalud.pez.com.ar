<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Safety guard: tests MUST NOT ever run against the local MySQL database.
        // Many tests use RefreshDatabase, which will drop/recreate tables.
        if (! app()->environment('testing')) {
            throw new RuntimeException('Tests must run with APP_ENV=testing.');
        }

        $defaultConnection = (string) config('database.default');
        $dbName = (string) (config("database.connections.{$defaultConnection}.database") ?? '');

        if ($defaultConnection !== 'sqlite' || $dbName !== ':memory:') {
            throw new RuntimeException(
                "Unsafe test database configuration. Expected sqlite/:memory:, got {$defaultConnection}/{$dbName}. " .
                'Aborting to avoid data loss.'
            );
        }
    }
}
