<?php

namespace Tests\Unit\Actions\Admin\ServerLog;

use App\Api\Admin\Actions\ServerLog\ClearServerLogAction;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ClearServerLogActionTest extends TestCase
{
    private ClearServerLogAction $action;

    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ClearServerLogAction::class);
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

    public function test_execute_truncates_the_log_file(): void
    {
        $name = 'test-clear-'.uniqid().'.log';
        $path = $this->makeLogFile($name, 'some log content');

        $this->action->execute($name);

        $this->assertSame('', File::get($path));
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
