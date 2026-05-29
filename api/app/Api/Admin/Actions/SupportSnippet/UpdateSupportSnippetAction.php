<?php

namespace App\Api\Admin\Actions\SupportSnippet;

use App\Models\SupportSnippet;

class UpdateSupportSnippetAction
{
    public function execute(SupportSnippet $snippet, array $data): SupportSnippet
    {
        $snippet->update($data);

        return $snippet;
    }
}
