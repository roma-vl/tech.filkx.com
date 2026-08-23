<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ExportAdminUsersAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ExportAdminUsersActionTest extends TestCase
{
    use RefreshDatabase;

    private ExportAdminUsersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ExportAdminUsersAction::class);
    }

    private function streamedContent(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    public function test_execute_streams_a_csv_with_a_header_row_and_one_row_per_user(): void
    {
        $user = User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        $response = $this->action->execute([]);
        $csv = $this->streamedContent($response);

        $this->assertStringContainsString('ID,Name,Email,Plan,Status', $csv);
        $this->assertStringContainsString((string) $user->id, $csv);
        $this->assertStringContainsString('Alice', $csv);
        $this->assertStringContainsString('alice@example.com', $csv);
        $this->assertStringContainsString('active', $csv);
    }

    public function test_execute_reports_soft_deleted_users_as_deleted(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->delete();

        $response = $this->action->execute(['with_deleted' => true]);
        $csv = $this->streamedContent($response);

        $this->assertStringContainsString('deleted', $csv);
    }

    public function test_execute_applies_the_given_filters(): void
    {
        User::factory()->create(['name' => 'Match Me', 'email' => 'match@example.com']);
        User::factory()->create(['name' => 'Someone Else', 'email' => 'else@example.com']);

        $response = $this->action->execute(['search' => 'Match']);
        $csv = $this->streamedContent($response);

        $this->assertStringContainsString('Match Me', $csv);
        $this->assertStringNotContainsString('Someone Else', $csv);
    }

    public function test_execute_returns_a_csv_streamed_response_with_download_headers(): void
    {
        $response = $this->action->execute([]);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame('text/csv', $response->headers->get('Content-type'));
        $this->assertStringContainsString('attachment; filename=clients_export.csv', $response->headers->get('Content-Disposition'));
    }
}
