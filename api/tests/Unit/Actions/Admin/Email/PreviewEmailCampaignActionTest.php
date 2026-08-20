<?php

namespace Tests\Unit\Actions\Admin\Email;

use App\Api\Admin\Actions\Email\PreviewEmailCampaignAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Actions\Admin\Email\Fakes\FakeCampaignMail;

class PreviewEmailCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    private PreviewEmailCampaignAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(PreviewEmailCampaignAction::class);
    }

    public function test_execute_throws_for_a_class_that_does_not_exist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid campaign class.');

        $this->action->execute('App\\Mail\\Campaigns\\DoesNotExist');
    }

    public function test_execute_renders_the_campaign_for_the_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Ivan']);
        $this->actingAs($user);

        $result = $this->action->execute(FakeCampaignMail::class);

        $this->assertSame('Hello Ivan', $result);
    }

    public function test_execute_renders_the_campaign_when_there_is_no_authenticated_user(): void
    {
        $result = $this->action->execute(FakeCampaignMail::class);

        $this->assertSame('Hello guest', $result);
    }
}
