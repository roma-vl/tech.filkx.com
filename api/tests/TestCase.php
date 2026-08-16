<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Passport's personal access tokens (User::createToken()) need a personal access
        // client to exist. Production/dev DBs get one via `passport:install`; the
        // in-memory sqlite test DB starts empty, so create one whenever the schema is
        // fresh for this test (i.e. it uses RefreshDatabase).
        if (in_array(RefreshDatabase::class, class_uses_recursive(static::class), true)
            && Schema::hasTable('oauth_clients')) {
            $this->artisan('passport:client', [
                '--personal' => true,
                '--name' => 'Testing Personal Access Client',
                '--provider' => 'users',
                '--no-interaction' => true,
            ]);
        }
    }
}
