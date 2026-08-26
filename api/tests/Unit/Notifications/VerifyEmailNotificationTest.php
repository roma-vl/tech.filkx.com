<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_picks_the_subject_from_the_recipients_locale(): void
    {
        $ukUser = User::factory()->create(['locale' => 'uk']);
        $enUser = User::factory()->create(['locale' => 'en']);

        $notification = new VerifyEmailNotification;

        $this->assertSame('Підтвердження електронної адреси', $notification->toMail($ukUser)->subject);
        $this->assertSame('Verify Email Address', $notification->toMail($enUser)->subject);
    }

    public function test_it_renders_ukrainian_body_content_for_a_uk_locale_user(): void
    {
        $user = User::factory()->create(['locale' => 'uk', 'name' => 'Роман']);

        $html = (new VerifyEmailNotification)->toMail($user)->render();

        $this->assertStringContainsString('Ласкаво просимо до', $html);
        $this->assertStringContainsString('Підтвердити електронну адресу', $html);
        $this->assertStringNotContainsString('Verify Email Address', $html);
    }

    public function test_it_renders_english_body_content_for_an_en_locale_user(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'name' => 'Roman']);

        $html = (new VerifyEmailNotification)->toMail($user)->render();

        $this->assertStringContainsString('Welcome to', $html);
        $this->assertStringContainsString('Verify Email Address', $html);
        $this->assertStringNotContainsString('Ласкаво просимо', $html);
    }

    public function test_it_defaults_to_ukrainian_when_the_notifiable_has_no_locale(): void
    {
        $notifiable = new class
        {
            public string $name = 'Guest';

            public function getKey(): int
            {
                return 1;
            }

            public function getEmailForVerification(): string
            {
                return 'guest@example.com';
            }
        };

        $mail = (new VerifyEmailNotification)->toMail($notifiable);

        $this->assertSame('Підтвердження електронної адреси', $mail->subject);
    }
}
