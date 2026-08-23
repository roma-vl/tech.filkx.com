<?php

namespace Tests\Unit\Actions\Admin\ServerLog;

use App\Api\Admin\Actions\ServerLog\ShowServerLogAction;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ShowServerLogActionTest extends TestCase
{
    private ShowServerLogAction $action;

    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ShowServerLogAction::class);
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

    private function makeLogFile(string $name, string $content): string
    {
        $path = storage_path('logs/'.$name);
        File::put($path, $content);
        $this->createdFiles[] = $path;

        return $path;
    }

    public function test_execute_returns_the_filename_and_content(): void
    {
        $name = 'test-show-'.uniqid().'.log';
        $this->makeLogFile($name, "line one\nline two\nline three\n");

        $result = $this->action->execute($name);

        $this->assertSame($name, $result['name']);
        $this->assertStringContainsString('line one', $result['content']);
        $this->assertStringContainsString('line three', $result['content']);
    }

    public function test_execute_reads_a_file_whose_last_line_has_no_trailing_newline(): void
    {
        $name = 'test-show-no-newline-'.uniqid().'.log';
        $this->makeLogFile($name, "line one\nline two");

        $result = $this->action->execute($name);

        $this->assertStringContainsString('line one', $result['content']);
        $this->assertStringContainsString('line two', $result['content']);
    }

    public function test_execute_rejects_a_filename_with_path_traversal_characters(): void
    {
        $this->expectException(HttpException::class);

        try {
            $this->action->execute('../secrets.log');
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_execute_throws_not_found_when_the_log_file_does_not_exist(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->action->execute('does-not-exist-'.uniqid().'.log');
    }
}
