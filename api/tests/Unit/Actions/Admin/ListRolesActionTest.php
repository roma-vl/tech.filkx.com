<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ListRolesAction;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListRolesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListRolesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListRolesAction::class);
    }

    public function test_execute_returns_all_seeded_roles_paginated(): void
    {
        $seeded = Role::count();

        $result = $this->action->execute();

        $this->assertSame($seeded, $result->total());
    }

    public function test_execute_respects_a_custom_per_page(): void
    {
        Role::create(['name' => 'Extra 1', 'slug' => 'extra-1', 'scope' => 'global']);
        Role::create(['name' => 'Extra 2', 'slug' => 'extra-2', 'scope' => 'global']);

        $result = $this->action->execute(['per_page' => 1]);

        $this->assertCount(1, $result->items());
    }
}
