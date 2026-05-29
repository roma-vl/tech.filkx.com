<?php

namespace App\Api\Admin\Actions\SupportSnippet;

use App\Models\SupportSnippet;

class DeleteSupportSnippetAction
{
    public function execute(SupportSnippet $snippet): void
    {
        $snippet->delete();
    }
}
