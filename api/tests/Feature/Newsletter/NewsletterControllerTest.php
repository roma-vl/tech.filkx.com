<?php

namespace Tests\Feature\Newsletter;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_subscribes_a_valid_email(): void
    {
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'reader@example.com',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.com']);
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'not-an-email']);
    }

    public function test_subscribing_twice_with_the_same_email_does_not_error(): void
    {
        $this->postJson('/api/v1/newsletter/subscribe', ['email' => 'dup@example.com'])->assertOk();
        $this->postJson('/api/v1/newsletter/subscribe', ['email' => 'dup@example.com'])->assertOk();

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }
}
