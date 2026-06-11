<?php

namespace App\Api\Admin\Actions\SupportSnippet;

use App\Models\SupportSnippet;
use Illuminate\Database\Eloquent\Collection;

class ListSupportSnippetsAction
{
    public function execute(): Collection
    {
        return SupportSnippet::latest()->get();
    }
}
