<?php

namespace App\Api\Admin\Actions\SupportSnippet;

use App\Models\SupportSnippet;

class CreateSupportSnippetAction
{
    public function execute(array $data): SupportSnippet
    {
        return SupportSnippet::create($data);
    }
}
