<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_the_suite_runs_against_the_postgres_test_database()
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertSame('deliberia_test', DB::connection()->getDatabaseName());
    }
}
