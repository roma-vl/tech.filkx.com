<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\StoreSupportAttachmentAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreSupportAttachmentActionTest extends TestCase
{
    use RefreshDatabase;

    private StoreSupportAttachmentAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(StoreSupportAttachmentAction::class);
    }

    public function test_execute_returns_all_nulls_when_no_file_is_given(): void
    {
        $result = $this->action->execute(null);

        $this->assertSame([
            'file_path' => null,
            'file_type' => null,
            'file_name' => null,
            'file_size' => null,
        ], $result);
    }

    public function test_execute_stores_the_file_and_returns_its_metadata(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('attachment.pdf', 100, 'application/pdf');

        $result = $this->action->execute($file);

        Storage::disk('public')->assertExists($result['file_path']);
        $this->assertStringStartsWith('support_files/', $result['file_path']);
        $this->assertSame('attachment.pdf', $result['file_name']);
        $this->assertSame('application/pdf', $result['file_type']);
        $this->assertSame(100 * 1024, $result['file_size']);
    }
}
