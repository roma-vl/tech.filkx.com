<?php

namespace Tests\Unit\Actions\Admin\ServerLog;

use App\Api\Admin\Actions\ServerLog\ListServerLogsAction;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ListServerLogsActionTest extends TestCase
{
    private ListServerLogsAction $action;

    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListServerLogsAction::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    private function makeLogFile(string $name, string $content, int $mtime): string
    {
        $path = storage_path('logs/'.$name);
        File::put($path, $content);
        touch($path, $mtime);
        $this->createdFiles[] = $path;

        return $path;
    }

    public function test_execute_lists_files_with_name_size_and_updated_at(): void
    {
        $name = 'test-list-'.uniqid().'.log';
        $this->makeLogFile($name, '12345', time());

        $result = $this->action->execute();
        $entry = $result->firstWhere('name', $name);

        $this->assertNotNull($entry);
        $this->assertSame(5, $entry['size']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $entry['updated_at']);
    }

    public function test_execute_sorts_files_by_most_recently_updated_first(): void
    {
        $older = 'test-list-a-'.uniqid().'.log';
        $newer = 'test-list-b-'.uniqid().'.log';
        $this->makeLogFile($older, 'x', time() - 3600);
        $this->makeLogFile($newer, 'x', time());

        $result = $this->action->execute()->values();
        $names = $result->pluck('name')->all();

        $this->assertLessThan(array_search($older, $names), array_search($newer, $names));
    }
}
