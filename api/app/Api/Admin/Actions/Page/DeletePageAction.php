<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;

class DeletePageAction
{
    public function execute(int $id): void
    {
        Page::findOrFail($id)->delete();
    }
}
