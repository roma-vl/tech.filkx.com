<?php

namespace Tests\Feature\Index;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_index_returns_a_success_payload(): void
    {
        $response = $this->getJson('/api/index');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.api', 'Hello Api!!!');
    }
}
